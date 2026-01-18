<?php
session_start();

//Powrót
$link_powrotu = "index.php";
$tekst_powrotu = "Strona główna";

// Jeśli ktoś jest zalogowany, zmieniamy link na jego dashboard
if (isset($_SESSION['rola'])) {
    $rola = $_SESSION['rola'];
    if ($rola == 'uczen') {
        $link_powrotu = "uczen/uczen.php";
        $tekst_powrotu = "Wróć do panelu";
    } elseif ($rola == 'nauczyciel') {
        $link_powrotu = "nauczyciel/nauczyciel.php";
        $tekst_powrotu = "Wróć do panelu";
    } elseif ($rola == "rodzic") {
        $link_powrotu = "rodzic/rodzic.php";
        $tekst_powrotu = "Wróć do panelu";
    } elseif ($rola == 'admin') {
        $link_powrotu = "admin/admin.php";
        $tekst_powrotu = "Wróć do panelu";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Informacje o szkole</title>
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background-color: #f4f6f9; 
            margin: 0; padding: 0;
            height: 100vh;
        }

        .top-nav { padding: 20px 40px; }

        .btn-home {
            background-color: white; color: #555; text-decoration: none; font-weight: 600;
            padding: 10px 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border: 1px solid #ddd; display: inline-block; transition: 0.3s;
        }
        .btn-home:hover { background-color: #eee; color: #333; }

        .container { max-width: 700px; margin: 0 auto; text-align: center; padding-top: 20px; }

        h1 { color: #333; font-size: 40px; font-weight: normal; margin-bottom: 60px; }

        .info-box {
            background: white; padding: 40px; border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }

        .info-row {
            padding: 25px 0; border-bottom: 1px solid #ddd;
            display: flex; justify-content: center; align-items: center; gap: 20px; flex-wrap: wrap;
        }
        .info-row:last-child { border-bottom: none; }

        .label { font-size: 18px; color: #666; text-align: right; min-width: 150px; }
        .value { font-size: 22px; font-weight: bold; color: #333; text-align: left; }
        .value.address { color: #2196F3; }
    </style>
</head>
<body>

    <div class="top-nav">
        <a href="<?php echo $link_powrotu; ?>" class="btn-home">← <?php echo $tekst_powrotu; ?></a>
    </div>

    <div class="container">
        <h1>Informacje</h1>
        <div class="info-box">
            <div class="info-row">
                <div class="label">Telefon:</div>
                <div class="value">444 333 111</div>
            </div>
            <div class="info-row">
                <div class="label">Godziny otwarcia<br>sekretariatu:</div>
                <div class="value">Pon. - Pt. 9 - 14</div>
            </div>
            <div class="info-row">
                <div class="label">Adres:</div>
                <div class="value address">ul. 11 Listopada, Wrocław</div>
            </div>
        </div>
    </div>

</body>
</html>