<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['rola']) || $_SESSION['rola'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['dodaj_przedmiot'])) {
    $nazwa = $_POST['nazwa'];

    try {
        $stmt = $conn->prepare("INSERT INTO Przedmiot (nazwa) VALUES (:nazwa)");
        $stmt->bindParam(':nazwa', $nazwa);
        $stmt->execute();
        $message = "Dodano przedmiot: $nazwa";
    } catch (PDOException $e) {
        $message = "Błąd: " . $e->getMessage();
    }
}

$stmt = $conn->query("SELECT * FROM Przedmiot ORDER BY nazwa");
$przedmioty = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Zarządzanie Przedmiotami</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #eee; }
        input { padding: 8px; width: 300px; }
        button { padding: 8px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        .btn-back { display: inline-block; margin-bottom: 15px; text-decoration: none; color: #333; }
    </style>
</head>
<body>

<div class="container">
    <a href="admin.php" class="btn-back">← Wróć do panelu</a>
    <h2>Zarządzanie Przedmiotami</h2>

    <?php if ($message): ?>
        <p style="color: green; font-weight: bold;"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="nazwa" placeholder="Nazwa przedmiotu (np. Matematyka)" required>
        <button type="submit" name="dodaj_przedmiot">Dodaj przedmiot</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nazwa Przedmiotu</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($przedmioty as $p): ?>
            <tr>
                <td><?php echo $p['id_przedmiotu']; ?></td>
                <td><?php echo $p['nazwa']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>