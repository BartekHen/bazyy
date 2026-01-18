<?php
session_start();
require_once '../db.php';

// Zabezpieczenie: tylko uczeń lub rodzic
if (!isset($_SESSION['rola']) || !in_array($_SESSION['rola'], ['uczen', 'rodzic'])) {
    header("Location: ../index.php");
    exit;
}

// 1. Pobieramy klasę zalogowanego ucznia
$stmt = $conn->prepare("SELECT id_klasy FROM Uczen WHERE id_uzytkownika = :uid");
$stmt->execute([':uid' => $_SESSION['user_id']]);
$id_klasy = $stmt->fetchColumn();

if (!$id_klasy) {
    die("Błąd: Nie jesteś przypisany do żadnej klasy.");
}

// 2. Pobieramy przedmioty tej klasy wraz z informacją o terminach
// Zwróć uwagę na kolumnę 'terminy' - tam siedzi nasz zakodowany plan
$sql = "SELECT p.nazwa as przedmiot, u.imie, u.nazwisko, pwk.terminy
        FROM Przedmiot_w_klasie pwk
        JOIN Przedmiot p ON pwk.id_przedmiotu = p.id_przedmiotu
        JOIN Nauczyciel n ON pwk.id_nauczyciela = n.id_nauczyciela
        JOIN Uzytkownik u ON n.id_uzytkownika = u.id_uzytkownika
        WHERE pwk.id_klasy = :id";

$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $id_klasy]);
$przedmioty = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Przetwarzamy dane (Rozpakowujemy stringi "1_1,1_2" do tablicy PHP)
// Struktura docelowa: $plan[DZIEN][GODZINA] = ['przedmiot' => ..., 'nauczyciel' => ...]
$plan = [];

foreach ($przedmioty as $row) {
    // Sprawdzamy czy nauczyciel w ogóle ustawił jakieś terminy dla tego przedmiotu
    if (!empty($row['terminy'])) {
        // Rozbijamy string po przecinku na tablicę, np. ["1_1", "3_4"]
        $sloty = explode(',', $row['terminy']);
        
        foreach ($sloty as $slot) {
            // Rozbijamy "1_1" na Dzień i Godzinę
            $czesci = explode('_', $slot);
            if (count($czesci) == 2) {
                $dzien = $czesci[0];
                $godzina = $czesci[1];
                
                // Zapisujemy do głównej tablicy planu
                $plan[$dzien][$godzina] = [
                    'przedmiot' => $row['przedmiot'],
                    'nauczyciel' => $row['imie'] . ' ' . $row['nazwisko']
                ];
            }
        }
    }
}

// Dane pomocnicze do wyświetlania
$dni_tygodnia = [1 => 'Poniedziałek', 2 => 'Wtorek', 3 => 'Środa', 4 => 'Czwartek', 5 => 'Piątek'];
$godziny_lekcyjne = [
    1 => '8:00 - 8:45',
    2 => '8:55 - 9:40',
    3 => '9:50 - 10:35',
    4 => '10:45 - 11:30',
    5 => '11:45 - 12:30',
    6 => '12:40 - 13:25',
    7 => '13:35 - 14:20',
    8 => '14:25 - 15:10'
];
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Mój Plan Lekcji</title>
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background-color: #f4f6f9; 
            margin: 0; padding: 20px;
        }

        .container { 
            max-width: 1100px; margin: 0 auto; 
            background: white; padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
        }
        
        .top-bar { margin-bottom: 20px; }
        .btn-home { 
            text-decoration: none; color: #555; font-weight: bold; font-size: 15px; 
            display: inline-flex; align-items: center; gap: 5px;
        }
        .btn-home:hover { color: #000; }

        h1 { 
            text-align: center; color: #2c3e50; margin-bottom: 30px; font-weight: 600;
        }

        /* --- STYL TABELI --- */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            table-layout: fixed; /* Stała szerokość kolumn */
            margin-top: 10px;
        }
        
        /* Nagłówek (Dni tygodnia) */
        thead th { 
            background-color: #3498db; 
            color: white; 
            padding: 15px; 
            text-transform: uppercase; 
            font-size: 13px; letter-spacing: 1px;
            border-right: 1px solid rgba(255,255,255,0.2);
        }
        thead th:first-child { border-top-left-radius: 8px; }
        thead th:last-child { border-top-right-radius: 8px; }

        /* Wiersze */
        tbody td { 
            border: 1px solid #eee; 
            padding: 5px; 
            height: 90px; /* Wysokość komórki */
            vertical-align: top; 
        }

        /* Kolumna z godzinami */
        td.hour-col { 
            background-color: #fafafa; 
            text-align: center; 
            vertical-align: middle; 
            color: #555; 
            width: 110px;
            border-right: 2px solid #eee;
        }
        .hour-nr { font-size: 18px; font-weight: bold; color: #333; display: block; }
        .hour-time { font-size: 11px; color: #888; }

        /* Karta Lekcji wewnątrz komórki */
        .lesson-card { 
            background-color: #e3f2fd; /* Jasny niebieski */
            border-left: 4px solid #2196F3; /* Pasek akcentowy */
            border-radius: 4px; 
            padding: 8px; 
            height: 100%; 
            box-sizing: border-box; /* Żeby padding nie rozwalał wysokości */
            display: flex; 
            flex-direction: column; 
            justify-content: center;
        }

        .subject-name { 
            font-weight: bold; 
            color: #1565C0; 
            font-size: 14px; 
            margin-bottom: 4px;
        }
        
        .teacher-name { 
            font-size: 11px; 
            color: #555; 
        }

        /* Pusta lekcja */
        .empty-cell { 
            color: #e0e0e0; 
            font-size: 24px; 
            text-align: center; 
            display: block; 
            margin-top: 25px;
        }

    </style>
</head>
<body>

    <div class="container">
        <div class="top-bar">
            <a href="dziennik.php" class="btn-home">← Wróć do przeglądu</a>
        </div>

        <h1>Twój Tygodniowy Plan Zajęć</h1>

        <table>
            <thead>
                <tr>
                    <th style="width: 100px;">Godz.</th>
                    <?php foreach ($dni_tygodnia as $nazwa) echo "<th>$nazwa</th>"; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($godziny_lekcyjne as $nr => $czas): ?>
                <tr>
                    <td class="hour-col">
                        <span class="hour-nr"><?php echo $nr; ?></span>
                        <span class="hour-time"><?php echo $czas; ?></span>
                    </td>
                    
                    <?php foreach ($dni_tygodnia as $nr_dnia => $nazwa_dnia): ?>
                        <td>
                            <?php if (isset($plan[$nr_dnia][$nr])): ?>
                                <?php $lekcja = $plan[$nr_dnia][$nr]; ?>
                                <div class="lesson-card">
                                    <span class="subject-name"><?php echo $lekcja['przedmiot']; ?></span>
                                    <span class="teacher-name"><?php echo $lekcja['nauczyciel']; ?></span>
                                </div>
                            <?php else: ?>
                                <span class="empty-cell">&bull;</span>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>