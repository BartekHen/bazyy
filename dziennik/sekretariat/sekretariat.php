<?php
session_start();
// ZMIANA: Sprawdzamy, czy to sekretariat
if (!isset($_SESSION['rola']) || $_SESSION['rola'] !== 'sekretariat') {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel Sekretariatu</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; text-align: center; }
        .container { max-width: 800px; margin: 50px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { margin-bottom: 40px; color: #333; }
        
        /* Układ kafelków */
        .menu-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        
        .menu-btn { 
            display: block; 
            padding: 20px; 
            background-color: #e0e0e0; 
            color: black; 
            text-decoration: none; 
            border-radius: 5px; 
            border: 1px solid #ccc;
            font-size: 18px;
            transition: background 0.3s;
        }
        .menu-btn:hover { background-color: #d0d0d0; }
        
        .logout-btn { 
            display: inline-block; 
            margin-top: 30px; 
            padding: 10px 20px; 
            background-color: #ff4444; 
            color: white; 
            text-decoration: none; 
            border-radius: 5px; 
        }
    </style>
</head>
<body>

<div class="container">
    <p style="text-align: right;">Zalogowany jako: <strong>Sekretariat</strong></p>
    <h1>Strona główna</h1>

    <div class="menu-grid">
        <a href="klasy.php" class="menu-btn">Lista klas</a>
        <a href="zarzadzaj_uzytkownikami.php" class="menu-btn">Edytuj konto uzcznia lub rodzica</a>
        
        <a href="przypisz_do_rodzica.php" class="menu-btn">Przypisz ucznia do rodzica</a>
        
        <a href="import.php" class="menu-btn">Importuj rodziców i uczniów</a>
    </div>

    <a href="../logout.php" class="logout-btn">Wyloguj</a>
</div>

</body>
</html>