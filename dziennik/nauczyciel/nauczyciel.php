<?php
session_start();

// Zabezpieczenie: tylko nauczyciel
if (!isset($_SESSION['rola']) || $_SESSION['rola'] !== 'nauczyciel') {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel Nauczyciela</title>
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background-color: #f4f6f9; 
            margin: 0; 
            padding: 0; 
            min-height: 100vh;
            position: relative; 
        }

        /* Nagłówek */
        .header {
            text-align: right;
            padding: 20px 40px;
            color: #555;
            font-size: 14px;
        }

        /* Główny kontener */
        .main-container {
            max-width: 900px;
            margin: 0 auto;
            text-align: center;
            padding-top: 20px;
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 60px;
            font-weight: 600;
        }

        /* --- UKŁAD KAFELKÓW  */
        
        /* Górny rząd: Lekcje i Kalendarz */
        .top-row {
            display: flex;
            justify-content: center;
            gap: 60px; /* Odstęp między kafelkami */
            margin-bottom: 50px;
        }

        /* Dolny rząd: Informacje */
        .bottom-row {
            display: flex;
            justify-content: center;
        }

        /* Kafelki*/
        .card {
            background: white;
            width: 250px;
            padding: 30px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            text-decoration: none;
            color: #333;
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            border-top: 4px solid transparent; /* Kolorowy pasek na górze */
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .card .icon {
            font-size: 40px;
            margin-bottom: 15px;
        }

        .card h2 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
        }

        .card p {
            font-size: 13px;
            color: #777;
            margin-top: 8px;
        }

        /* Kolory dla konkretnych kart */
        .card-lekcje { border-top-color: #007bff; }    /* Niebieski */
        .card-kalendarz { border-top-color: #ffc107; } /* Żółty */
        .card-info { border-top-color: #17a2b8; }      /* Turkusowy */


        /* --- PRZYCISK WYLOGUJ (Lewy dolny róg) --- */
        .btn-logout {
            position: fixed;
            bottom: 30px;
            left: 30px;
            background-color: #fff;
            color: #dc3545;
            border: 2px solid #dc3545;
            padding: 10px 25px;
            text-decoration: none;
            font-weight: bold;
            border-radius: 50px;
            transition: 0.3s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .btn-logout:hover {
            background-color: #dc3545;
            color: white;
        }

    </style>
</head>
<body>

    <div class="header">
        Zalogowano jako nauczyciel: <strong><?php echo $_SESSION['imie'] . ' ' . $_SESSION['nazwisko']; ?></strong>
    </div>

    <div class="main-container">
        
        <h1>Strona główna</h1>

        <div class="top-row">
            
            <a href="lekcje.php" class="card card-lekcje">
                <div class="icon">📚</div>
                <h2>Lekcje</h2>
                <p>Kliknij, aby przejść</p>
            </a>

            <a href="sprawdzian.php" class="card card-kalendarz">
                <div class="icon">📅</div>
                <h2>Kalendarz</h2>
                <p>Sprawdziany i wydarzenia</p>
            </a>

        </div>

        <div class="bottom-row">
            
            <a href="../informacje.php" class="card card-info">
                <div class="icon">🏫</div>
                <h2>Informacje</h2>
                <p>Przeglądaj informacje o szkole</p>
            </a>

        </div>

    </div>

    <a href="../logout.php" class="btn-logout">Wyloguj</a>

</body>
</html>