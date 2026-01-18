<?php
session_start();
require_once '../db.php';

// 1. Zabezpieczenie: tylko rodzic
if (!isset($_SESSION['rola']) || $_SESSION['rola'] !== 'rodzic') {
    header("Location: ../index.php");
    exit;
}
if (!isset($_GET['id_uzytkownika'])) {
    header("Location: rodzic.php");
    exit;
}

$target_id = $_GET['id_uzytkownika'];
$my_id = $_SESSION['user_id'];

// 2. WERYFIKACJA: Czy rodzic ma prawo do tego dziecka?
$check = $conn->prepare("
    SELECT ucz.id_ucznia 
    FROM Opieka o 
    JOIN Rodzic r ON o.id_rodzica = r.id_rodzica 
    JOIN Uczen ucz ON o.id_ucznia = ucz.id_ucznia 
    JOIN Uzytkownik u ON ucz.id_uzytkownika = u.id_uzytkownika 
    WHERE r.id_uzytkownika = :my_id AND u.id_uzytkownika = :target_id
");
$check->execute([':my_id' => $my_id, ':target_id' => $target_id]);
$dane_ucznia = $check->fetch(PDO::FETCH_ASSOC);

if (!$dane_ucznia) {
    die("Brak dostępu lub nie znaleziono ucznia.");
}

$id_ucznia = $dane_ucznia['id_ucznia'];

// 3. POBIERANIE NIEOBECNOŚCI
// Poprawione zapytanie SQL pasujące do Twojego zrzutu ekranu
// Łączymy: nieobecnosc -> Lekcja -> Przedmiot_w_klasie -> Przedmiot
$sql = "SELECT l.data, p.nazwa as przedmiot, n.usprawiedliwiona, n.powod
        FROM nieobecnosc n
        JOIN Lekcja l ON n.id_lekcji = l.id_lekcji
        JOIN Przedmiot_w_klasie pwk ON l.id_przedmiot_w_klasie = pwk.id_przedmiot_w_klasie
        JOIN Przedmiot p ON pwk.id_przedmiotu = p.id_przedmiotu
        WHERE n.id_ucznia = :uid
        ORDER BY l.data DESC";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([':uid' => $id_ucznia]);
    $lista = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $lista = []; 
    $error = "Błąd bazy danych: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Obecności</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        .btn-back { text-decoration: none; color: #555; border: 1px solid #ccc; padding: 5px 15px; border-radius: 4px; display:inline-block; margin-bottom: 20px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #FF9800; color: white; padding: 10px; text-align: left; }
        td { border-bottom: 1px solid #eee; padding: 10px; }
        
        /* Statusy */
        .badge { padding: 5px 10px; border-radius: 10px; font-weight: bold; font-size: 12px; }
        .b-nb { background: #ffebee; color: #c62828; } /* Czerwony - Nieobecny */
        .b-u { background: #e8f5e9; color: #2e7d32; }   /* Zielony - Usprawiedliwiony */
    </style>
</head>
<body>
    <div class="container">
        <a href="panel.php?id_uzytkownika=<?php echo $target_id; ?>" class="btn-back">← Wróć do menu</a>
        <h2>Rejestr Nieobecności</h2>

        <?php if(isset($error)): ?>
            <div style="background: #ffebee; color: #c62828; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <strong>Wystąpił błąd:</strong> <?php echo $error; ?>
                <br><br>
                <em>Upewnij się, że masz w bazie tabelę <strong>Lekcja</strong>, która zawiera kolumny <code>id_lekcji</code> oraz <code>data</code>.</em>
            </div>
        <?php endif; ?>

        <?php if(count($lista) > 0): ?>
            <table>
                <thead><tr><th>Data</th><th>Przedmiot</th><th>Status</th><th>Powód</th></tr></thead>
                <tbody>
                    <?php foreach($lista as $w): ?>
                    <tr>
                        <td><strong><?php echo $w['data']; ?></strong></td>
                        <td><?php echo $w['przedmiot']; ?></td>
                        <td>
                            <?php if($w['usprawiedliwiona'] == 1): ?>
                                <span class="badge b-u">Usprawiedliwione</span>
                            <?php else: ?>
                                <span class="badge b-nb">Nieobecny</span>
                            <?php endif; ?>
                        </td>
                        <td style="color:#777; font-size:13px;">
                            <?php echo $w['powod']; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <?php if(!isset($error)): ?>
                <p style="text-align:center; padding:30px; color:green; font-weight:bold;">100% Frekwencji! 👏</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>