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
    <title>Przegląd dziennika</title>
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; /* Nowoczesna czcionka */
            background-color: #f4f6f9; /* Delikatne szare tło */
            margin: 0; padding: 0; height: 100vh;
        }

        /* Górny pasek z przyciskiem powrotu */
        .top-bar {
            padding: 15px 40px;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .btn-home {
            text-decoration: none; color: #555; font-weight: 600; font-size: 15px;
            display: flex; align-items: center; gap: 8px;
            padding: 8px 15px; border-radius: 5px; transition: 0.3s;
        }
        .btn-home:hover { background-color: #eee; color: #333; }
        .btn-home i { font-size: 18px; } /* Ikona strzałki */

        .main-container {
            max-width: 1000px; margin: 0 auto;
            text-align: center; padding-top: 50px;
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 60px;
            font-weight: 600;
        }

        /* Kontener na 3 sekcje obok siebie */
        .buttons-row {
            display: flex;
            justify-content: center;
            gap: 60px; /* Odstępy między sekcjami */
            flex-wrap: wrap;
        }

        /* Pojedyncza sekcja (tytuł + przycisk) */
        .menu-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            /* Nowy styl sekcji: biała karta z cieniem */
            background: white;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            width: 220px; /* Stała szerokość */
        }
        
        .menu-item:hover { transform: translateY(-5px); }

        .menu-label {
            font-size: 20px; font-weight: 600; color: #444;
            margin-bottom: 25px;
        }

        /* Nowoczesny przycisk "Kliknij" */
        .btn-kliknij {
            background-color: #3498db; /* Ładny niebieski */
            border: none;
            color: white;
            padding: 12px 50px;
            text-decoration: none;
            font-size: 16px; font-weight: bold;
            border-radius: 50px; /* Zaokrąglone rogi */
            transition: 0.3s;
            box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
        }
        .btn-kliknij:hover {
            background-color: #2980b9;
            box-shadow: 0 6px 15px rgba(52, 152, 219, 0.4);
        }

        /* Kolory przycisków dla różnych sekcji */
        .btn-oceny { background-color: #4CAF50; box-shadow: 0 4px 10px rgba(76, 175, 80, 0.3); }
        .btn-oceny:hover { background-color: #388E3C; }

        .btn-frek { background-color: #FF9800; box-shadow: 0 4px 10px rgba(255, 152, 0, 0.3); }
        .btn-frek:hover { background-color: #F57C00; }

    </style>
</head>
<body>

    <div class="top-bar">
        <a href="uczen.php" class="btn-home">
            <i>←</i> Strona główna
        </a>
    </div>

    <div class="main-container">
        <h1>Przegląd dziennika</h1>

        <div class="buttons-row">
            
            <div class="menu-item">
                <div class="menu-label">Oceny</div>
                <a href="oceny.php" class="btn-kliknij btn-oceny">Kliknij</a>
            </div>

            <div class="menu-item">
                <div class="menu-label">Plan lekcji</div>
                <a href="plan.php" class="btn-kliknij">Kliknij</a>
            </div>

            <div class="menu-item">
                <div class="menu-label">Obecności</div>
                <a href="obecnosc.php" class="btn-kliknij btn-frek">Kliknij</a>
            </div>

        </div>
    </div>

</body>
</html>