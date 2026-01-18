<?php
session_start();
require_once '../db.php';

// Zabezpieczenie: tylko uczeń lub rodzic
if (!isset($_SESSION['rola']) || !in_array($_SESSION['rola'], ['uczen', 'rodzic'])) {
    header("Location: ../index.php");
    exit;
}

// Pobieramy ID ucznia i jego klasę
$stmt = $conn->prepare("SELECT id_ucznia, id_klasy FROM Uczen WHERE id_uzytkownika = :uid");
$stmt->execute([':uid' => $_SESSION['user_id']]);
$dane_ucznia = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dane_ucznia || !$dane_ucznia['id_klasy']) {
    die("<div style='text-align:center; padding:50px;'>Nie jesteś przypisany do żadnej klasy. Poproś administratora o przypisanie.</div>");
}

$id_ucznia = $dane_ucznia['id_ucznia'];
$id_klasy = $dane_ucznia['id_klasy'];


$sql = "SELECT 
            p.nazwa as przedmiot, 
            n.imie as n_imie, n.nazwisko as n_nazwisko,
            o.wartosc, o.waga, o.typ, o.data_wystawienia
        FROM Przedmiot_w_klasie pwk
        JOIN Przedmiot p ON pwk.id_przedmiotu = p.id_przedmiotu
        JOIN Nauczyciel tech ON pwk.id_nauczyciela = tech.id_nauczyciela
        JOIN Uzytkownik n ON tech.id_uzytkownika = n.id_uzytkownika
        LEFT JOIN Ocena o ON pwk.id_przedmiot_w_klasie = o.id_przedmiot_w_klasie AND o.id_ucznia = :uid
        WHERE pwk.id_klasy = :kid
        ORDER BY p.nazwa ASC, o.data_wystawienia DESC";

$stmt = $conn->prepare($sql);
$stmt->execute([':uid' => $id_ucznia, ':kid' => $id_klasy]);
$wyniki = $stmt->fetchAll(PDO::FETCH_ASSOC);

//Grupujemy wyniki według przedmiotu
$dziennik = [];

foreach ($wyniki as $row) {
    $przedmiot = $row['przedmiot'];
    
    // Inicjalizacja przedmiotu w tablicy 
    if (!isset($dziennik[$przedmiot])) {
        $dziennik[$przedmiot] = [
            'nauczyciel' => $row['n_imie'] . ' ' . $row['n_nazwisko'],
            'oceny' => []
        ];
    }

    
    if ($row['wartosc'] !== null) {
        $dziennik[$przedmiot]['oceny'][] = [
            'wartosc' => $row['wartosc'],
            'waga' => $row['waga'],
            'typ' => $row['typ'], 
            'data' => $row['data_wystawienia']
        ];
    }
}


function obliczSrednia($oceny) {
    if (empty($oceny)) return "-";
    
    $suma_liczb = 0;
    $suma_wag = 0;

    foreach ($oceny as $o) {
        $str = $o['wartosc'];
        $waga = (int)$o['waga'];
        
        // Konwersja znaków na liczby
        $wartosc = floatval($str); 
        if (strpos($str, '+') !== false) $wartosc += 0.5;
        if (strpos($str, '-') !== false) $wartosc -= 0.25;

        
        if ($wartosc > 0) {
            $suma_liczb += $wartosc * $waga;
            $suma_wag += $waga;
        }
    }

    if ($suma_wag == 0) return "-";
    return round($suma_liczb / $suma_wag, 2);
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Moje Oceny</title>
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background-color: #f4f6f9; 
            margin: 0; padding: 20px;
        }

        .container { 
            max-width: 1000px; margin: 0 auto; 
            background: white; padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
        }

        /* Pasek powrotu */
        .top-bar { margin-bottom: 20px; }
        .btn-home { 
            text-decoration: none; color: #555; font-weight: bold; 
            display: inline-flex; align-items: center; gap: 5px;
        }
        .btn-home:hover { color: #000; }

        h1 { text-align: center; color: #2c3e50; margin-bottom: 30px; font-weight: 600; }

        /* TABELA */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        
        thead th { 
            background-color: #4CAF50; /* Zielony nagłówek dla ocen */
            color: white; 
            padding: 15px; 
            text-align: left; 
            font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;
        }

        tbody tr { border-bottom: 1px solid #eee; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background-color: #fcfcfc; }

        td { padding: 15px; vertical-align: middle; }

        /* Stylizacja kolumn */
        .col-subject { width: 25%; color: #333; }
        .subject-name { font-weight: bold; font-size: 16px; display: block; }
        .teacher-name { font-size: 12px; color: #888; margin-top: 3px; }

        .col-grades { width: 60%; }
        
        .col-avg { width: 15%; text-align: center; font-weight: bold; font-size: 18px; color: #333; }

        /* KAFELKI Z OCENAMI */
        .grade-badge {
            display: inline-block;
            width: 32px; height: 32px;
            line-height: 32px;
            text-align: center;
            margin: 3px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 14px;
            cursor: help; /* Zmienia kursor na znak zapytania */
            box-shadow: 1px 1px 3px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            position: relative;
        }
        .grade-badge:hover { transform: scale(1.1); z-index: 10; }

        /* Kolory ocen */
        .g-1 { background-color: #ffcdd2; color: #c62828; border: 1px solid #e57373; } /* 1 - Czerwony */
        .g-2 { background-color: #ffe0b2; color: #ef6c00; border: 1px solid #ffb74d; } /* 2 - Pomarańczowy */
        .g-3 { background-color: #fff9c4; color: #fbc02d; border: 1px solid #fff176; } /* 3 - Żółty */
        .g-4 { background-color: #e1f5fe; color: #0277bd; border: 1px solid #4fc3f7; } /* 4 - Niebieski */
        .g-5 { background-color: #c8e6c9; color: #2e7d32; border: 1px solid #81c784; } /* 5 - Zielony */
        .g-6 { background-color: #d1c4e9; color: #512da8; border: 1px solid #9575cd; } /* 6 - Fioletowy */

        .avg-high { color: #2e7d32; } /* Dobra średnia */
        .avg-low { color: #c62828; }  /* Zagrożenie */

    </style>
</head>
<body>

    <div class="container">
        <div class="top-bar">
            <a href="dziennik.php" class="btn-home">← Wróć do przeglądu</a>
        </div>

        <h1>Twoje Oceny</h1>

        <table>
            <thead>
                <tr>
                    <th>Przedmiot</th>
                    <th>Oceny cząstkowe</th>
                    <th style="text-align:center;">Średnia</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dziennik as $przedmiot => $dane): ?>
                <?php 
                    $srednia = obliczSrednia($dane['oceny']);
                    // Styl koloru średniej
                    $avg_class = "";
                    if ($srednia != "-" && $srednia >= 4.75) $avg_class = "avg-high";
                    if ($srednia != "-" && $srednia < 2.0) $avg_class = "avg-low";
                ?>
                <tr>
                    <td class="col-subject">
                        <span class="subject-name"><?php echo $przedmiot; ?></span>
                        <span class="teacher-name"><?php echo $dane['nauczyciel']; ?></span>
                    </td>
                    
                    <td class="col-grades">
                        <?php if (empty($dane['oceny'])): ?>
                            <span style="color:#ccc; font-style:italic; font-size:13px;">Brak ocen</span>
                        <?php else: ?>
                            <?php foreach ($dane['oceny'] as $o): ?>
                                <?php 
                                    // Ustalanie koloru kafelka na podstawie pierwszej cyfry oceny
                                    $cyfra = substr($o['wartosc'], 0, 1);
                                    $klasa_koloru = "g-" . $cyfra; 
                                    
                                    // Budowanie opisu do dymka (tooltip)
                                    $tooltip = "Ocena: " . $o['wartosc'] . "\n" .
                                               "Waga: " . $o['waga'] . "\n" .
                                               "Opis: " . $o['typ'] . "\n" . 
                                               "Data: " . $o['data'];
                                ?>
                                <div class="grade-badge <?php echo $klasa_koloru; ?>" title="<?php echo $tooltip; ?>">
                                    <?php echo $o['wartosc']; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>

                    <td class="col-avg <?php echo $avg_class; ?>">
                        <?php echo $srednia; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>

</body>
</html>