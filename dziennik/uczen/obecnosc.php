<?php
session_start();
require_once '../db.php';

// Zabezpieczenie
if (!isset($_SESSION['rola']) || !in_array($_SESSION['rola'], ['uczen', 'rodzic'])) {
    header("Location: ../index.php");
    exit;
}

// 1. ID ucznia
$stmt = $conn->prepare("SELECT id_ucznia FROM Uczen WHERE id_uzytkownika = :uid");
$stmt->execute([':uid' => $_SESSION['user_id']]);
$id_ucznia = $stmt->fetchColumn();

// 2. Pobieramy nieobecności
$sql = "SELECT l.data, l.temat, p.nazwa as przedmiot, n.powod
        FROM Nieobecnosc n
        JOIN Lekcja l ON n.id_lekcji = l.id_lekcji
        JOIN Przedmiot_w_klasie pwk ON l.id_przedmiot_w_klasie = pwk.id_przedmiot_w_klasie
        JOIN Przedmiot p ON pwk.id_przedmiotu = p.id_przedmiotu
        WHERE n.id_ucznia = :uid
        ORDER BY l.data DESC";

$stmt = $conn->prepare($sql);
$stmt->execute([':uid' => $id_ucznia]);
$nieobecnosci = $stmt->fetchAll(PDO::FETCH_ASSOC);

$liczba = count($nieobecnosci);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Moja Frekwencja</title>
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background-color: #f4f6f9; /* Tło całego systemu */
            margin: 0; padding: 0;
            height: 100vh;
        }

        /* Górny pasek (jak w poprzednim pliku) */
        .top-bar {
            padding: 15px 40px;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 40px;
        }

        .btn-home {
            text-decoration: none; color: #555; font-weight: 600; font-size: 15px;
            display: flex; align-items: center; gap: 8px;
            padding: 8px 15px; border-radius: 5px; transition: 0.3s;
        }
        .btn-home:hover { background-color: #eee; color: #333; }

        /* Główny kontener */
        .container {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
            padding: 0 20px;
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 40px;
            font-weight: 600;
        }

        /* --- 1. SEKCJA LICZNIKA (Na środku, jak na makiecie, ale ładniej) --- */
        .summary-card {
            background: white;
            display: inline-block;
            padding: 30px 60px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border-top: 5px solid #FF9800; /* Pomarańczowy akcent (kolor frekwencji) */
            margin-bottom: 50px;
        }

        .summary-count {
            display: block;
            font-size: 48px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .summary-label {
            font-size: 16px;
            color: #777;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* --- 2. TABELA (Na dole) --- */
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            overflow: hidden; /* Zaokrągla rogi tabeli */
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background-color: #f8f9fa;
            color: #555;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            border-bottom: 2px solid #eee;
        }

        tbody td {
            padding: 15px;
            border-bottom: 1px solid #f4f4f4;
            text-align: left;
            color: #333;
            font-size: 15px;
        }

        /* Status "Nieobecny" - nowoczesna pigułka */
        .status-badge {
            background-color: #ffebee;
            color: #c62828;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }

        /* Pusty stan (Gdy 0 nieobecności) */
        .empty-msg {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            color: #2e7d32;
            font-weight: bold;
            border-top: 5px solid #4CAF50;
        }

    </style>
</head>
<body>

    <div class="top-bar">
        <a href="dziennik.php" class="btn-home">← Powrót</a>
    </div>

    <div class="container">
        
        <h1>Rejestr nieobecności</h1>

        <div class="summary-card">
            <span class="summary-count"><?php echo $liczba; ?></span>
            <span class="summary-label">Nieobecności</span>
        </div>

        <?php if ($liczba > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 150px;">Data</th>
                            <th>Przedmiot</th>
                            <th>Temat lekcji</th>
                            <th style="width: 120px; text-align:center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($nieobecnosci as $n): ?>
                        <tr>
                            <td style="font-weight:bold; color:#555;">
                                <?php echo $n['data']; ?>
                            </td>
                            <td style="color:#1976D2; font-weight:600;">
                                <?php echo $n['przedmiot']; ?>
                            </td>
                            <td>
                                <?php echo $n['temat']; ?>
                            </td>
                            <td style="text-align:center;">
                                <span class="status-badge">NIEOBECNY</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-msg">
                Świetnie! Nie masz żadnych nieobecności.
            </div>
        <?php endif; ?>

    </div>

</body>
</html>