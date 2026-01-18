<?php
session_start();
require_once '../db.php';

// 1. Zabezpieczenie: Czy to rodzic?
if (!isset($_SESSION['rola']) || $_SESSION['rola'] !== 'rodzic') {
    header("Location: ../index.php");
    exit;
}

// 2. Czy podano ID dziecka?
if (!isset($_GET['id_uzytkownika_dziecka'])) {
    header("Location: rodzic.php");
    exit;
}

$id_rodzica_user = $_SESSION['user_id'];
$target_id = $_GET['id_uzytkownika_dziecka'];

// 3. WERYFIKACJA: Czy rodzic ma prawo do tego dziecka?
$check = $conn->prepare("
    SELECT u.imie, u.nazwisko, ucz.id_ucznia, ucz.id_klasy
    FROM Opieka o
    JOIN Rodzic r ON o.id_rodzica = r.id_rodzica
    JOIN Uczen ucz ON o.id_ucznia = ucz.id_ucznia
    JOIN Uzytkownik u ON ucz.id_uzytkownika = u.id_uzytkownika
    WHERE r.id_uzytkownika = :my_id AND u.id_uzytkownika = :target_id
");
$check->execute([':my_id' => $id_rodzica_user, ':target_id' => $target_id]);
$dziecko = $check->fetch(PDO::FETCH_ASSOC);

if (!$dziecko) {
    die("<h2 style='text-align:center;margin-top:50px;color:red'>Brak dostępu do danych tego ucznia.</h2>");
}

$id_ucznia = $dziecko['id_ucznia'];
$id_klasy = $dziecko['id_klasy'];

// 4. POBIERANIE OCEN
// Pobieramy przedmioty przypisane do klasy i oceny ucznia (LEFT JOIN)
$sql = "SELECT 
            p.nazwa as przedmiot, 
            n.imie as n_imie, n.nazwisko as n_nazwisko,
            o.wartosc, o.waga, o.typ, o.data_wystawienia
        FROM Przedmiot_w_klasie pwk
        JOIN Przedmiot p ON pwk.id_przedmiotu = p.id_przedmiotu
        JOIN Nauczyciel tech ON pwk.id_nauczyciela = tech.id_nauczyciela
        JOIN Uzytkownik n ON tech.id_uzytkownika = n.id_uzytkownika
        LEFT JOIN Ocena o ON pwk.id_przedmiot_w_klasie = o.id_przedmiot_w_klasie 
                          AND o.id_ucznia = :uid
        WHERE pwk.id_klasy = :kid
        ORDER BY p.nazwa ASC, o.data_wystawienia DESC";

$stmt = $conn->prepare($sql);
$stmt->execute([':uid' => $id_ucznia, ':kid' => $id_klasy]);
$wyniki = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. GRUPOWANIE WYNIKÓW
// Tworzymy tablicę, gdzie kluczem jest nazwa przedmiotu
$dziennik = [];
foreach ($wyniki as $row) {
    $przedmiot = $row['przedmiot'];
    if (!isset($dziennik[$przedmiot])) {
        $dziennik[$przedmiot] = [
            'nauczyciel' => $row['n_imie'].' '.$row['n_nazwisko'], 
            'oceny' => []
        ];
    }
    // Jeśli ocena istnieje (bo LEFT JOIN może zwrócić NULL dla przedmiotu bez ocen)
    if ($row['wartosc'] !== null) {
        $dziennik[$przedmiot]['oceny'][] = [
            'wartosc' => $row['wartosc'], 
            'waga' => $row['waga'], 
            'typ' => $row['typ']
        ];
    }
}

// Funkcja do obliczania średniej ważonej
function obliczSrednia($oceny) {
    if (empty($oceny)) return "-";
    $suma = 0; 
    $wagi = 0;
    foreach ($oceny as $o) {
        // Obsługa plusów i minusów (np. 4+ to 4.5)
        $val = floatval($o['wartosc']);
        if (strpos($o['wartosc'], '+') !== false) $val += 0.5;
        if (strpos($o['wartosc'], '-') !== false) $val -= 0.25;
        
        if ($val > 0) { // Ignorujemy "np" itp.
            $suma += $val * $o['waga'];
            $wagi += $o['waga'];
        }
    }
    return ($wagi == 0) ? "-" : round($suma / $wagi, 2);
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Oceny: <?php echo $dziecko['imie']; ?></title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; }
        .btn-back { text-decoration: none; color: #555; border: 1px solid #ccc; padding: 8px 15px; border-radius: 5px; font-weight: bold; background: white; }
        .btn-back:hover { background: #eee; }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #4CAF50; color: white; padding: 15px; text-align: left; }
        td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
        tr:hover { background-color: #f9f9f9; }

        /* Styl samych ocen (kafelki z cyfrą) */
        .grade-badge { 
            display: inline-block; width: 32px; height: 32px; 
            line-height: 32px; text-align: center; margin: 2px; 
            border-radius: 4px; font-weight: bold; font-size: 14px;
            cursor: help; border: 1px solid rgba(0,0,0,0.1);
        }
        
        /* Kolory ocen */
        .g-1 { background: #ffcdd2; color: #c62828; } /* Czerwony */
        .g-2 { background: #ef9a9a; color: #c62828; }
        .g-3 { background: #ffe0b2; color: #e65100; } /* Pomarańcz */
        .g-4 { background: #fff9c4; color: #fbc02d; } /* Żółty */
        .g-5 { background: #c8e6c9; color: #2e7d32; } /* Zielony */
        .g-6 { background: #a5d6a7; color: #1b5e20; }
        
        .avg { font-weight: bold; font-size: 16px; color: #333; }
        .teacher-name { font-size: 12px; color: #888; display: block; margin-top: 4px; }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <div>
                <h2 style="margin:0;">Oceny ucznia</h2>
                <span style="color:#666;"><?php echo $dziecko['imie'] . " " . $dziecko['nazwisko']; ?></span>
            </div>
            <a href="panel.php?id_uzytkownika=<?php echo $target_id; ?>" class="btn-back">← Wróć do menu dziecka</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="30%">Przedmiot</th>
                    <th width="60%">Oceny cząstkowe</th>
                    <th width="10%">Średnia</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dziennik as $przedmiot => $dane): ?>
                <tr>
                    <td>
                        <strong><?php echo $przedmiot; ?></strong>
                        <span class="teacher-name"><?php echo $dane['nauczyciel']; ?></span>
                    </td>
                    <td>
                        <?php if(empty($dane['oceny'])): ?>
                            <span style="color:#ccc; font-style:italic;">Brak ocen</span>
                        <?php else: ?>
                            <?php foreach ($dane['oceny'] as $o): ?>
                                <?php $cyfra = substr($o['wartosc'], 0, 1); ?>
                                <span class="grade-badge g-<?php echo is_numeric($cyfra) ? $cyfra : 'default'; ?>" 
                                      title="Waga: <?php echo $o['waga']; ?>, Typ: <?php echo $o['typ']; ?>">
                                    <?php echo $o['wartosc']; ?>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="avg"><?php echo obliczSrednia($dane['oceny']); ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>