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
    header("Location: dashboard.php");
    exit;
}

$id_rodzica_user = $_SESSION['user_id'];
$id_dziecka_user = $_GET['id_uzytkownika'];

// 3. WERYFIKACJA: Czy rodzic ma prawo do tego dziecka? (Bezpieczeństwo)
$sql = "SELECT u.imie, u.nazwisko 
        FROM Opieka o
        JOIN Rodzic r ON o.id_rodzica = r.id_rodzica
        JOIN Uczen ucz ON o.id_ucznia = ucz.id_ucznia
        JOIN Uzytkownik u ON ucz.id_uzytkownika = u.id_uzytkownika
        WHERE r.id_uzytkownika = :id_rodzica AND u.id_uzytkownika = :id_dziecka";

$stmt = $conn->prepare($sql);
$stmt->execute([':id_rodzica' => $id_rodzica_user, ':id_dziecka' => $id_dziecka_user]);
$dziecko = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dziecko) {
    die("Błąd: Nie masz uprawnień do przeglądania danych tego ucznia.");
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel ucznia: <?php echo $dziecko['imie']; ?></title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        
        /* Nagłówek */
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .header h1 { margin: 0; color: #333; font-weight: 300; }
        .header h2 { margin: 5px 0 0 0; font-size: 24px; font-weight: bold; color: #2196F3; }
        
        .btn-back { 
            text-decoration: none; color: #555; background: white; 
            padding: 10px 20px; border-radius: 5px; border: 1px solid #ccc; font-weight: bold;
            transition: 0.3s;
        }
        .btn-back:hover { background: #eee; }

        /* Siatka menu */
        .menu-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); 
            gap: 25px; 
        }

        /* Kafelki menu */
        .menu-card {
            background: white; padding: 30px; border-radius: 12px;
            text-align: center; text-decoration: none; color: #333;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.2s;
            border-top: 5px solid #ccc;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            height: 180px;
        }
        .menu-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        
        .icon { font-size: 48px; margin-bottom: 15px; display: block; }
        .label { font-size: 20px; font-weight: bold; }
        .desc { color: #777; font-size: 13px; margin-top: 5px; }

        /* Kolory kafelków */
        .c-oceny { border-color: #4CAF50; }
        .c-oceny .icon { color: #4CAF50; }
        
        .c-plan { border-color: #2196F3; }
        .c-plan .icon { color: #2196F3; }
        
        .c-frekw { border-color: #FF9800; }
        .c-frekw .icon { color: #FF9800; }

        
        .c-terminarz { border-color: #E91E63; }
        .c-terminarz .icon { color: #E91E63; }

    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div>
            <h1>Panel Rodzica</h1>
            <h2>Uczeń: <?php echo $dziecko['imie'] . " " . $dziecko['nazwisko']; ?></h2>
        </div>
        <a href="rodzic.php" class="btn-back">← Wróć do listy dzieci</a>
    </div>

    <div class="menu-grid">
        
        <a href="oceny.php?id_uzytkownika_dziecka=<?php echo $id_dziecka_user; ?>" class="menu-card c-oceny">
            <span class="icon">📊</span>
            <span class="label">Oceny</span>
            <span class="desc">Oceny cząstkowe i średnie</span>
        </a>

        <a href="plan.php?id_uzytkownika=<?php echo $id_dziecka_user; ?>" class="menu-card c-plan">
            <span class="icon">📅</span>
            <span class="label">Plan Lekcji</span>
            <span class="desc">Harmonogram zajęć</span>
        </a>

        <a href="frekwencja.php?id_uzytkownika=<?php echo $id_dziecka_user; ?>" class="menu-card c-frekw">
            <span class="icon">✋</span>
            <span class="label">Obecności</span>
            <span class="desc">Weryfikuj frekwencję</span>
        </a>

        <a href="kalendarz.php?id_uzytkownika=<?php echo $id_dziecka_user; ?>" class="menu-card c-terminarz">
            <span class="icon">📝</span>
            <span class="label">Sprawdziany</span>
            <span class="desc">Sprawdziany i kartkówki</span>
        </a>

    </div>
</div>

</body>
</html>