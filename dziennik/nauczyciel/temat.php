<?php
session_start();
require_once '../db.php';

// Zabezpieczenia
if (!isset($_SESSION['rola']) || $_SESSION['rola'] !== 'nauczyciel') {
    header("Location: ../index.php");
    exit;
}
if (!isset($_SESSION['aktywne_zajecia_id'])) {
    header("Location: lekcje.php");
    exit;
}

$id_zajec = $_SESSION['aktywne_zajecia_id'];
$komunikat = "";

// DODAWANIE NOWEJ LEKCJI 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['dodaj_temat'])) {
    $temat = trim($_POST['temat']);
    $data = $_POST['data'];

    if (!empty($temat) && !empty($data)) {
        try {
            $stmt = $conn->prepare("INSERT INTO Lekcja (temat, data, id_przedmiot_w_klasie) VALUES (:temat, :data, :id_zajec)");
            $stmt->bindParam(':temat', $temat);
            $stmt->bindParam(':data', $data);
            $stmt->bindParam(':id_zajec', $id_zajec);
            $stmt->execute();
            $komunikat = "Dodano nowy temat lekcji.";
        } catch (PDOException $e) {
            $komunikat = "Błąd: " . $e->getMessage();
        }
    } else {
        $komunikat = "Proszę podać temat i datę.";
    }
}

// POBIERANIE INFORMACJI O KLASIE 
$stmt_info = $conn->prepare("
    SELECT k.nazwa as klasa, p.nazwa as przedmiot 
    FROM Przedmiot_w_klasie pwk
    JOIN Klasa k ON pwk.id_klasy = k.id_klasy
    JOIN Przedmiot p ON pwk.id_przedmiotu = p.id_przedmiotu
    WHERE pwk.id_przedmiot_w_klasie = :id
");
$stmt_info->bindParam(':id', $id_zajec);
$stmt_info->execute();
$lekcja_info = $stmt_info->fetch(PDO::FETCH_ASSOC);

// POBIERANIE LISTY LEKCJI 
$stmt_lekcje = $conn->prepare("SELECT * FROM Lekcja WHERE id_przedmiot_w_klasie = :id ORDER BY data DESC");
$stmt_lekcje->bindParam(':id', $id_zajec);
$stmt_lekcje->execute();
$lista_lekcji = $stmt_lekcje->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Tematy lekcji</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px; }
        
        .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; background: white; padding: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-radius: 5px; }
        .btn-powrot { padding: 8px 20px; border: 1px solid #ccc; background: #fff; text-decoration: none; color: #333; border-radius: 5px; }
        
        .info-box { text-align: center; font-size: 20px; margin-bottom: 20px; color: #555; }
        
        .form-container { 
            background: white; 
            padding: 20px; 
            border-radius: 5px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
            margin-bottom: 30px;
            text-align: center;
        }
        
        input[type="text"], input[type="date"] { padding: 10px; margin: 5px; border: 1px solid #ccc; border-radius: 4px; font-family: Arial; width: 300px; }
        
        .btn-dodaj { background-color: #00bcd4; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 4px; font-weight: bold; }
        .btn-dodaj:hover { background-color: #0097a7; }

        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
        th { background-color: #eee; padding: 12px; border-bottom: 2px solid #ddd; text-align: left; }
        td { border-bottom: 1px solid #ddd; padding: 12px; }
        
        .btn-obecnosc { 
            text-decoration: none; 
            background-color: #4CAF50; 
            color: white; 
            padding: 5px 10px; 
            border-radius: 3px; 
            font-size: 14px; 
        }
        .btn-obecnosc:hover { background-color: #45a049; }

        .msg { color: green; font-weight: bold; margin-bottom: 10px; text-align: center; }
    </style>
</head>
<body>

    <div class="header-bar">
        <a href="lekcje.php" class="btn-powrot">← Powrót</a>
        <div>Zalogowany: <strong><?php echo $_SESSION['imie'] . ' ' . $_SESSION['nazwisko']; ?></strong></div>
    </div>

    <div class="info-box">
        Tematy lekcji: <strong style="color: #00bcd4;"><?php echo $lekcja_info['klasa']; ?> - <?php echo $lekcja_info['przedmiot']; ?></strong>
    </div>

    <?php if ($komunikat): ?>
        <div class="msg"><?php echo $komunikat; ?></div>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST">
            <h3>Dodaj nową lekcję</h3>
            <label>Data:</label>
            <input type="date" name="data" value="<?php echo date('Y-m-d'); ?>" required>
            <br>
            <label>Temat:</label>
            <input type="text" name="temat" placeholder="Wpisz temat zajęć..." required>
            <br><br>
            <button type="submit" name="dodaj_temat" class="btn-dodaj">Zapisz temat</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 150px;">Data</th>
                <th>Temat lekcji</th>
                <th style="width: 150px; text-align: center;">Akcje</th>
            </tr>
        </thead>    
        <tbody>
            <?php foreach ($lista_lekcji as $l): ?>
            <tr>
                <td><?php echo $l['data']; ?></td>
                <td><?php echo $l['temat']; ?></td>
                <td style="text-align: center;">
                    <a href="obecnosc.php?id_lekcji=<?php echo $l['id_lekcji']; ?>" class="btn-obecnosc">Sprawdź obecność</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>