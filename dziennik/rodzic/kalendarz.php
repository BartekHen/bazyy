<?php
session_start();
require_once '../db.php';

// 1. Zabezpieczenie: tylko rodzic
if (!isset($_SESSION['rola']) || $_SESSION['rola'] !== 'rodzic') {
    header("Location: ../index.php");
    exit;
}

// 2. Czy podano ID dziecka?
if (!isset($_GET['id_uzytkownika'])) {
    header("Location: rodzic.php");
    exit;
}

$id_rodzica_user = $_SESSION['user_id'];
$id_dziecka_user = $_GET['id_uzytkownika'];

// 3. WERYFIKACJA: Czy rodzic ma prawo do tego dziecka?
$check = $conn->prepare("
    SELECT u.imie, u.nazwisko, ucz.id_ucznia, ucz.id_klasy
    FROM Opieka o
    JOIN Rodzic r ON o.id_rodzica = r.id_rodzica
    JOIN Uczen ucz ON o.id_ucznia = ucz.id_ucznia
    JOIN Uzytkownik u ON ucz.id_uzytkownika = u.id_uzytkownika
    WHERE r.id_uzytkownika = :my_id AND u.id_uzytkownika = :child_id
");
$check->execute([':my_id' => $id_rodzica_user, ':child_id' => $id_dziecka_user]);
$dziecko = $check->fetch(PDO::FETCH_ASSOC);

if (!$dziecko) {
    die("<h2 style='text-align:center;margin-top:50px;color:red'>Brak dostępu do danych tego ucznia.</h2>");
}

$id_klasy = $dziecko['id_klasy'];

if (!$id_klasy) {
    die("<div style='text-align:center; padding:50px;'>To dziecko nie jest przypisane do żadnej klasy.</div>");
}

// 4. Pobieramy sprawdziany dla klasy dziecka
$sql = "SELECT s.data, s.typ, s.opis, 
               p.nazwa as przedmiot, 
               n_user.imie, n_user.nazwisko
        FROM Sprawdzian s
        JOIN Przedmiot_w_klasie pwk ON s.id_przedmiot_w_klasie = pwk.id_przedmiot_w_klasie
        JOIN Przedmiot p ON pwk.id_przedmiotu = p.id_przedmiotu
        JOIN Nauczyciel n ON pwk.id_nauczyciela = n.id_nauczyciela
        JOIN Uzytkownik n_user ON n.id_uzytkownika = n_user.id_uzytkownika
        WHERE pwk.id_klasy = :kid
        ORDER BY s.data ASC";

$stmt_s = $conn->prepare($sql);
$stmt_s->execute([':kid' => $id_klasy]);
$wydarzenia = $stmt_s->fetchAll(PDO::FETCH_ASSOC);

// Segregacja: Nadchodzące vs Historia
$nadchodzace = [];
$historia = [];
$dzis = date('Y-m-d');

foreach ($wydarzenia as $w) {
    if ($w['data'] >= $dzis) {
        $nadchodzace[] = $w;
    } else {
        $historia[] = $w;
    }
}
$historia = array_reverse($historia);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Kalendarz: <?php echo $dziecko['imie']; ?></title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f6f9; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }

        .top-bar { margin-bottom: 20px; display:flex; justify-content: space-between; align-items: center;}
        .btn-back { text-decoration: none; color: #555; background: white; padding: 8px 15px; border: 1px solid #ccc; border-radius: 5px; }

        h1 { color: #333; margin-top: 0; font-size: 24px; }
        .sub-header { color: #666; margin-top: -10px; margin-bottom: 20px; font-size: 14px; }
        
        h2 { border-bottom: 2px solid #ddd; padding-bottom: 10px; color: #444; margin-top: 40px; font-size: 18px; }

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
            text-align: center; min-width: 70px; margin-right: 20px;
        }
        .date-day { font-size: 18px; font-weight: bold; display: block; }
        .date-month { font-size: 11px; text-transform: uppercase; }

        .info-box { flex-grow: 1; }
        .subject { color: #1976D2; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .desc { font-size: 15px; color: #000; font-weight: 500; margin: 4px 0; }
        .teacher { font-size: 12px; color: #999; }

        .type-badge {
            padding: 5px 10px; border-radius: 20px; color: white; font-size: 11px; font-weight: bold; white-space: nowrap;
        }
        .b-sprawdzian { background: #e53935; } 
        .b-kartkowka { background: #fb8c00; } 
        .b-inne { background: #1e88e5; } 

        .history-section .event-row { opacity: 0.6; }
        .history-section .type-badge { background: #999; }
    </style>
</head>
<body>

<div class="container">
    <div class="top-bar">
        <div>
            <h1>Kalendarz ucznia</h1>
            <div class="sub-header">Dziecko: <strong><?php echo $dziecko['imie'] . " " . $dziecko['nazwisko']; ?></strong></div>
        </div>
        <a href="panel.php?id_uzytkownika=<?php echo $id_dziecka_user; ?>" class="btn-back">← Wróć do menu</a>
    </div>

    <div class="card">
        
        <?php if (count($nadchodzace) > 0): ?>
            <?php foreach ($nadchodzace as $w): ?>
                <?php 
                    $timestamp = strtotime($w['data']);
                    $dzien = date('d', $timestamp);
                    $miesiac = date('M', $timestamp);
                    
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
                Brak nadchodzących sprawdzianów dla Twojego dziecka.
            </div>
        <?php endif; ?>

    </div>

    <?php if (count($historia) > 0): ?>
        <h2>Historia</h2>
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