<?php
session_start();

if (!isset($_SESSION['rola']) || !in_array($_SESSION['rola'], ['uczen', 'rodzic'])) {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel Ucznia</title>
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background-color: #f4f6f9; 
            margin: 0; 
            padding: 0; 
            min-height: 100vh;
            position: relative; 
        }

        .header {
            text-align: right;
            padding: 20px 40px;
            color: #555;
            font-size: 14px;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .main-container {
            max-width: 900px;
            margin: 0 auto;
            text-align: center;
            padding-top: 40px;
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 60px;
            font-weight: 600;
        }

        /* --- STYL KAFELKÓW (MODERN) --- */
        .card {
            background: white;
            width: 240px;
            padding: 30px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            text-decoration: none;
            color: #333;
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            border-top: 4px solid transparent;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .card .icon { font-size: 40px; margin-bottom: 15px; }
        .card h2 { margin: 0; font-size: 18px; font-weight: bold; }
        .card p { font-size: 13px; color: #777; margin-top: 8px; }

        /* Kolory kafelków */
        .card-info { border-top-color: #2196F3; }      /* Niebieski */
        .card-kalendarz { border-top-color: #FF9800; } /* Pomarańczowy */
        .card-dziennik { border-top-color: #9C27B0; }  /* Fioletowy (Główny) */

        /* --- UKŁAD (LAYOUT) --- */
        .row-top {
            display: flex;
            justify-content: center;
            gap: 60px;
            margin-bottom: 50px;
        }

        .row-bottom {
            display: flex;
            justify-content: center;
        }

        /* Przycisk Wyloguj */
        .btn-logout {
            position: fixed; bottom: 30px; left: 30px;
            background-color: #fff; color: #dc3545; border: 2px solid #dc3545;
            padding: 10px 25px; text-decoration: none; font-weight: bold;
            border-radius: 50px; transition: 0.3s; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .btn-logout:hover { background-color: #dc3545; color: white; }

    </style>
</head>
<body>

    <div class="header">
        Zalogowany: <strong><?php echo $_SESSION['imie'] . ' ' . $_SESSION['nazwisko']; ?></strong>
    </div>

    <div class="main-container">
        <h1>Strona główna</h1>

        <div class="row-top">
            
            <a href="../informacje.php" class="card card-info">
                <div class="icon">📢</div>
                <h2>Informacje</h2>
                <p>Ogłoszenia szkolne</p>
            </a>

            <a href="kalendarz.php" class="card card-kalendarz">
                <div class="icon">📅</div>
                <h2>Kalendarz</h2>
                <p>Sprawdziany i wydarzenia</p>
            </a>

        </div>

        <div class="row-bottom">
            
            <a href="dziennik.php" class="card card-dziennik">
                <div class="icon">📚</div>
                <h2>Przeglądaj Dziennik</h2>
                <p>Oceny, Plan, Obecności</p>
            </a>

        </div>
    </div>

    <a href="../logout.php" class="btn-logout">Wyloguj</a>

</body>
</html>