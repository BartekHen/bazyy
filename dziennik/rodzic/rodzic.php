<?php
session_start();
require_once '../db.php'; // Upewnij się, że ścieżka do db.php jest poprawna

// Zabezpieczenie: tylko rodzic
if (!isset($_SESSION['rola']) || $_SESSION['rola'] !== 'rodzic') {
    header("Location: ../index.php");
    exit;
}

// Pobieramy dzieci przypisane do tego rodzica
$id_rodzica_user = $_SESSION['user_id'];

$sql = "SELECT u_dziecko.id_uzytkownika, u_dziecko.imie, u_dziecko.nazwisko, k.nazwa as klasa
        FROM Opieka o
        JOIN Rodzic r ON o.id_rodzica = r.id_rodzica
        JOIN Uczen ucz ON o.id_ucznia = ucz.id_ucznia
        JOIN Uzytkownik u_dziecko ON ucz.id_uzytkownika = u_dziecko.id_uzytkownika
        LEFT JOIN Klasa k ON ucz.id_klasy = k.id_klasy
        WHERE r.id_uzytkownika = :uid";

$stmt = $conn->prepare($sql);
$stmt->execute([':uid' => $id_rodzica_user]);
$dzieci = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel Rodzica</title>
    <style>
       
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; background: white; padding: 15px 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 40px; }
        .user-info { font-weight: bold; color: #555; }
        .btn-logout { color: #d32f2f; text-decoration: none; font-weight: bold; border: 1px solid #d32f2f; padding: 8px 15px; border-radius: 5px; transition: 0.3s; }
        .btn-logout:hover { background: #ffebee; }
        .btn-info { color: #1976D2; text-decoration: none; font-weight: bold; margin-right: 15px; }
        .container { max-width: 1000px; margin: 0 auto; text-align: center; }
        h1 { color: #333; font-weight: 300; margin-bottom: 10px; font-size: 32px; }
        p.subtitle { color: #777; margin-bottom: 40px; }
        .tiles-grid { display: flex; justify-content: center; flex-wrap: wrap; gap: 30px; }
        .tile { background: white; width: 260px; padding: 30px; border-radius: 15px; text-decoration: none; color: #333; box-shadow: 0 10px 25px rgba(0,0,0,0.05); transition: transform 0.3s ease, box-shadow 0.3s ease; position: relative; overflow: hidden; border-top: 5px solid #2196F3; }
        .tile:hover { transform: translateY(-10px); box-shadow: 0 15px 35px rgba(33, 150, 243, 0.2); }
        .tile-icon { font-size: 50px; margin-bottom: 15px; display: block; }
        .tile-name { font-size: 22px; font-weight: bold; display: block; margin-bottom: 5px; color: #2c3e50; }
        .tile-class { font-size: 14px; color: #888; background: #f1f1f1; padding: 4px 12px; border-radius: 20px; display: inline-block; }
        .tile-btn { margin-top: 20px; display: block; background-color: #e3f2fd; color: #1976D2; padding: 10px; border-radius: 8px; font-weight: bold; font-size: 14px; }
        .tile:hover .tile-btn { background-color: #2196F3; color: white; }
    </style>
</head>
<body>

    <div class="container">
        <div class="top-bar">
            <span class="user-info">Rodzic: <?php echo $_SESSION['imie'] . " " . $_SESSION['nazwisko']; ?></span>
            <div>
                <a href="../informacje.php" class="btn-info">ℹ Informacje</a>
                <a href="../logout.php" class="btn-logout">Wyloguj</a>
            </div>
        </div>

        <h1>Twoje Dzieci</h1>
        <p class="subtitle">Wybierz ucznia, aby zobaczyć szczegóły.</p>

        <div class="tiles-grid">
            <?php if (count($dzieci) > 0): ?>
                
                <?php foreach ($dzieci as $d): ?>
                    <a href="panel.php?id_uzytkownika=<?php echo $d['id_uzytkownika']; ?>" class="tile">
                        <span class="tile-icon">🎓</span>
                        <span class="tile-name"><?php echo $d['imie']; ?></span>
                        <span class="tile-class">Klasa <?php echo $d['klasa'] ?? 'Brak'; ?></span>
                        
                        <span class="tile-btn">Panel ucznia →</span>
                    </a>

                    
                <?php endforeach; ?>

            <?php else: ?>
                <div style="background: white; padding: 40px; border-radius: 10px; color: #777;">
                    Nie przypisano jeszcze żadnych uczniów do Twojego konta.
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>