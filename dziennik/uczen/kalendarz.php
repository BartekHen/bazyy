<?php
session_start();
require_once '../db.php';

// 1. Zabezpieczenie: tylko uczeń
if (!isset($_SESSION['rola']) || $_SESSION['rola'] !== 'uczen') {
    header("Location: ../index.php");
    exit;
}

$id_user = $_SESSION['user_id'];

// 2. Pobieramy ID KLASY tego ucznia
$stmt = $conn->prepare("SELECT id_klasy FROM Uczen WHERE id_uzytkownika = :uid");
$stmt->execute([':uid' => $id_user]);
$id_klasy = $stmt->fetchColumn();

if (!$id_klasy) {
    die("<div style='text-align:center; padding:50px; font-family:Arial;'>Nie jesteś przypisany do żadnej klasy. Skontaktuj się z sekretariatem.</div>");
}

// 3. Pobieramy sprawdziany dla tej klasy
// Łączymy tabele, żeby znać nazwę przedmiotu i nazwisko nauczyciela
$sql = "SELECT s.data, s.typ, s.opis, 
               p.nazwa as przedmiot, 
               u.imie, u.nazwisko
        FROM Sprawdzian s
        JOIN Przedmiot_w_klasie pwk ON s.id_przedmiot_w_klasie = pwk.id_przedmiot_w_klasie
        JOIN Przedmiot p ON pwk.id_przedmiotu = p.id_przedmiotu
        JOIN Nauczyciel n ON pwk.id_nauczyciela = n.id_nauczyciela
        JOIN Uzytkownik u ON n.id_uzytkownika = u.id_uzytkownika
        WHERE pwk.id_klasy = :kid
        ORDER BY s.data ASC"; // Sortujemy od najstarszych do najnowszych

$stmt_s = $conn->prepare($sql);
$stmt_s->execute([':kid' => $id_klasy]);
$wydarzenia = $stmt_s->fetchAll(PDO::FETCH_ASSOC);

// Rozdzielamy na nadchodzące i archiwalne (dla wygody widoku)
$nadchodzace = [];
$historia = [];
$dzis = date('Y-m-d');

foreach ($wydarzenia as $w) {
    if ($w['data'] >= $dzis) {
        $nadchodzace[] = $w;
    } else {
        $historia[] = $w; // Archiwum
    }
}
// Historię odwracamy, żeby widzieć te ostatnie na górze listy archiwalnej
$historia = array_reverse($historia);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Terminarz Sprawdzianów</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f6f9; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }

        .top-bar { margin-bottom: 20px; display:flex; justify-content: space-between; align-items: center;}
        .btn-back { text-decoration: none; color: #555; background: white; padding: 8px 15px; border: 1px solid #ccc; border-radius: 5px; }

        h1 { color: #333; margin-top: 0; }
        h2 { border-bottom: 2px solid #ddd; padding-bottom: 10px; color: #444; margin-top: 40px; }

        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }

        /* Styl kafelka wydarzenia */
        .event-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 15px; border-bottom: 1px solid #eee; transition: 0.2s;
        }
        .event-row:last-child { border-bottom: none; }
        .event-row:hover { background-color: #fafafa; }

        .date-box {
            background: #eee; color: #333; padding: 10px; border-radius: 8px; 
            text-align: center; min-width: 80px; margin-right: 20px;
        }
        .date-day { font-size: 20px; font-weight: bold; display: block; }
        .date-month { font-size: 12px; text-transform: uppercase; }

        .info-box { flex-grow: 1; }
        .subject { color: #888; font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .desc { font-size: 16px; color: #000; font-weight: 500; margin: 5px 0; }
        .teacher { font-size: 13px; color: #999; }

        .type-badge {
            padding: 5px 12px; border-radius: 20px; color: white; font-size: 12px; font-weight: bold; white-space: nowrap;
        }
        .b-sprawdzian { background: #e53935; } /* Czerwony */
        .b-kartkowka { background: #fb8c00; } /* Pomarańcz */
        .b-inne { background: #1e88e5; } /* Niebieski */

        /* Styl dla archiwum */
        .history-section .event-row { opacity: 0.6; }
        .history-section .type-badge { background: #999; }
    </style>
</head>
<body>

<div class="container">
    <div class="top-bar">
        <h1>📅 Twój Terminarz</h1>
        <a href="uczen.php" class="btn-back">← Wróć do panelu</a>
    </div>

    <div class="card">
        
        <?php if (count($nadchodzace) > 0): ?>
            <?php foreach ($nadchodzace as $w): ?>
                <?php 
                    $timestamp = strtotime($w['data']);
                    $dzien = date('d', $timestamp);
                    $miesiac = date('M', $timestamp); // np. Jan, Feb
                    
                    $klasa_badge = 'b-inne';
                    if($w['typ'] == 'Sprawdzian') $klasa_badge = 'b-sprawdzian';
                    if($w['typ'] == 'Kartkówka') $klasa_badge = 'b-kartkowka';
                ?>
                <div class="event-row">
                    <div class="date-box">
                        <span class="date-day"><?php echo $dzien; ?></span>
                        <span class="date-month"><?php echo $miesiac; ?></span>
                    </div>
                    <div class="info-box">
                        <div class="subject"><?php echo $w['przedmiot']; ?></div>
                        <div class="desc"><?php echo $w['opis']; ?></div>
                        <div class="teacher"><?php echo $w['imie'].' '.$w['nazwisko']; ?></div>
                    </div>
                    <div class="type-badge <?php echo $klasa_badge; ?>">
                        <?php echo $w['typ']; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="padding: 40px; text-align: center; color: #888;">
                Brak nadchodzących sprawdzianów. Możesz odpocząć! 😎
            </div>
        <?php endif; ?>

    </div>

    <?php if (count($historia) > 0): ?>
        <h2>Minione wydarzenia</h2>
        <div class="card history-section">
            <?php foreach ($historia as $w): ?>
                <div class="event-row">
                    <div class="date-box">
                        <span class="date-day"><?php echo date('d', strtotime($w['data'])); ?></span>
                        <span class="date-month"><?php echo date('m', strtotime($w['data'])); ?></span>
                    </div>
                    <div class="info-box">
                        <div class="subject"><?php echo $w['przedmiot']; ?></div>
                        <div class="desc"><?php echo $w['opis']; ?></div>
                    </div>
                    <div class="type-badge">
                        <?php echo $w['typ']; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

</body>
</html>