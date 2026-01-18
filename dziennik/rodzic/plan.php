<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['rola']) || $_SESSION['rola'] !== 'rodzic') { header("Location: ../index.php"); exit; }
if (!isset($_GET['id_uzytkownika'])) { header("Location: rodzic.php"); exit; }

$target_id = $_GET['id_uzytkownika'];
$my_id = $_SESSION['user_id'];

// 1. Zabezpieczenie (czy to moje dziecko?)
$check = $conn->prepare("SELECT 1 FROM Opieka o JOIN Rodzic r ON o.id_rodzica = r.id_rodzica JOIN Uczen ucz ON o.id_ucznia = ucz.id_ucznia JOIN Uzytkownik u ON ucz.id_uzytkownika = u.id_uzytkownika WHERE r.id_uzytkownika = :my_id AND u.id_uzytkownika = :target_id");
$check->execute([':my_id' => $my_id, ':target_id' => $target_id]);
if ($check->rowCount() == 0) die("Brak dostępu.");

// 2. Pobierz ID klasy dziecka
$stmt = $conn->prepare("SELECT id_klasy FROM Uczen WHERE id_uzytkownika = :uid");
$stmt->execute([':uid' => $target_id]);
$id_klasy = $stmt->fetchColumn();

if (!$id_klasy) die("<div style='text-align:center;padding:50px'>Dziecko nie jest przypisane do klasy.</div>");

// 3. Pobierz plan lekcji
$sql = "SELECT p.nazwa as przedmiot, u.imie, u.nazwisko, pwk.terminy
        FROM Przedmiot_w_klasie pwk
        JOIN Przedmiot p ON pwk.id_przedmiotu = p.id_przedmiotu
        JOIN Nauczyciel n ON pwk.id_nauczyciela = n.id_nauczyciela
        JOIN Uzytkownik u ON n.id_uzytkownika = u.id_uzytkownika
        WHERE pwk.id_klasy = :id";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $id_klasy]);
$przedmioty = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Przetwarzanie planu (format 1_1 -> poniedziałek 1 lekcja)
$plan = [];
foreach ($przedmioty as $row) {
    if (!empty($row['terminy'])) {
        $sloty = explode(',', $row['terminy']);
        foreach ($sloty as $slot) {
            $czesci = explode('_', $slot); // [Dzień, Godzina]
            if (count($czesci) == 2) {
                $plan[$czesci[0]][$czesci[1]] = [
                    'przedmiot' => $row['przedmiot'],
                    'nauczyciel' => $row['imie'] . ' ' . $row['nazwisko']
                ];
            }
        }
    }
}
$dni = [1 => 'Poniedziałek', 2 => 'Wtorek', 3 => 'Środa', 4 => 'Czwartek', 5 => 'Piątek'];
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Plan Lekcji</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        .btn-back { text-decoration: none; color: #555; border: 1px solid #ccc; padding: 5px 15px; border-radius: 4px; display:inline-block; margin-bottom: 20px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; table-layout: fixed; }
        th { background: #2196F3; color: white; padding: 10px; text-align: center; }
        td { border: 1px solid #ddd; padding: 5px; height: 80px; vertical-align: top; font-size: 13px; }
        
        .lesson { background: #e3f2fd; padding: 5px; border-radius: 4px; border-left: 3px solid #1976D2; }
        .nr { font-weight: bold; background: #eee; text-align: center; vertical-align: middle; width: 40px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="panel.php?id_uzytkownika=<?php echo $target_id; ?>" class="btn-back">← Wróć do menu</a>
        <h2>Plan Lekcji</h2>
        
        <table>
            <thead>
                <tr><th style="width:40px">Nr</th> <?php foreach ($dni as $d) echo "<th>$d</th>"; ?> </tr>
            </thead>
            <tbody>
                <?php for($h=1; $h<=8; $h++): ?>
                <tr>
                    <td class="nr"><?php echo $h; ?></td>
                    <?php foreach($dni as $nr_dnia => $nazwa): ?>
                        <td>
                            <?php if(isset($plan[$nr_dnia][$h])): ?>
                                <div class="lesson">
                                    <strong><?php echo $plan[$nr_dnia][$h]['przedmiot']; ?></strong><br>
                                    <span style="color:#666"><?php echo $plan[$nr_dnia][$h]['nauczyciel']; ?></span>
                                </div>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
</body>
</html>