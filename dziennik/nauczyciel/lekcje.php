<?php
session_start();
require_once '../db.php';

// Zabezpieczenie
if (!isset($_SESSION['rola']) || $_SESSION['rola'] !== 'nauczyciel') {
    header("Location: ../index.php");
    exit;
}

// Pobranie ID nauczyciela
$stmt = $conn->prepare("SELECT id_nauczyciela FROM Nauczyciel WHERE id_uzytkownika = :uid");
$stmt->bindParam(':uid', $_SESSION['user_id']);
$stmt->execute();
$nauczyciel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$nauczyciel) die("Błąd konta nauczyciela.");
$id_nauczyciela = $nauczyciel['id_nauczyciela'];

//Wybieranie klasy
$wybrana_klasa_id = isset($_GET['id_klasy']) ? $_GET['id_klasy'] : null;

// Pobieramy UNIKALNE klasy, w których uczy ten nauczyciel 
$sql_klasy = "SELECT DISTINCT k.id_klasy, k.nazwa 
              FROM Przedmiot_w_klasie pwk
              JOIN Klasa k ON pwk.id_klasy = k.id_klasy
              WHERE pwk.id_nauczyciela = :nid
              ORDER BY k.nazwa";
$stmt_klasy = $conn->prepare($sql_klasy);
$stmt_klasy->bindParam(':nid', $id_nauczyciela);
$stmt_klasy->execute();
$dostepne_klasy = $stmt_klasy->fetchAll(PDO::FETCH_ASSOC);

//Jeśli wybrano klasę, pobieramy przedmioty tego nauczyciela W TEJ klasie
$dostepne_przedmioty = [];
if ($wybrana_klasa_id) {
    $sql_przedmioty = "SELECT pwk.id_przedmiot_w_klasie, p.nazwa 
                       FROM Przedmiot_w_klasie pwk
                       JOIN Przedmiot p ON pwk.id_przedmiotu = p.id_przedmiotu
                       WHERE pwk.id_nauczyciela = :nid AND pwk.id_klasy = :kid
                       ORDER BY p.nazwa";
    $stmt_p = $conn->prepare($sql_przedmioty);
    $stmt_p->bindParam(':nid', $id_nauczyciela);
    $stmt_p->bindParam(':kid', $wybrana_klasa_id);
    $stmt_p->execute();
    $dostepne_przedmioty = $stmt_p->fetchAll(PDO::FETCH_ASSOC);
}

// Obsługa zatwierdzenia
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_pwk = $_POST['id_przedmiot_w_klasie']; // To jest ID konkretnego przydziału
    $akcja = $_POST['akcja'];

    if ($id_pwk) {
        $_SESSION['aktywne_zajecia_id'] = $id_pwk;

        if ($akcja == 'temat') header("Location: temat.php");
        elseif ($akcja == 'oceny') header("Location: oceny.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Wybór lekcji</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { max-width: 500px; margin: 50px auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; }
        .top-bar { max-width: 500px; margin: 0 auto 20px auto; }
        .btn-back { text-decoration: none; color: #555; font-weight: bold; }
        
        h1 { margin-bottom: 30px; color: #333; }
        
        label { display: block; text-align: left; margin-bottom: 5px; font-weight: bold; color: #555; }
        select { width: 100%; padding: 12px; font-size: 16px; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 20px; background: #fff; }
        
        .btn-action { width: 100%; padding: 15px; border: none; border-radius: 5px; font-size: 16px; font-weight: bold; cursor: pointer; color: white; margin-top: 10px; }
        .btn-tematy { background-color: #2196F3; }
        .btn-oceny { background-color: #4CAF50; }
        
        .hint { font-size: 14px; color: #777; margin-bottom: 15px; }
    </style>
    
    <script>
        // Funkcja do automatycznego przeładowania strony po wybraniu klasy
        function zmienKlase(selectObject) {
            var idKlasy = selectObject.value;
            if (idKlasy) {
                window.location.href = "lekcje.php?id_klasy=" + idKlasy;
            }
        }
    </script>
</head>
<body>

    <div class="top-bar">
        <a href="nauczyciel.php" class="btn-back">← Wróć do panelu głównego</a>
    </div>

    <div class="container">
        <h1>Wybór zajęć</h1>

        <form method="POST">
            
            <label>1. Wybierz klasę:</label>
            <select name="id_klasy" onchange="zmienKlase(this)">
                <option value="">-- Wybierz z listy --</option>
                <?php foreach ($dostepne_klasy as $k): ?>
                    <option value="<?php echo $k['id_klasy']; ?>" <?php if($wybrana_klasa_id == $k['id_klasy']) echo 'selected'; ?>>
                        Klasa <?php echo $k['nazwa']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if ($wybrana_klasa_id): ?>
                
                <label>2. Wybierz przedmiot:</label>
                <?php if (count($dostepne_przedmioty) > 0): ?>
                    <select name="id_przedmiot_w_klasie" required>
                        <?php foreach ($dostepne_przedmioty as $p): ?>
                            <option value="<?php echo $p['id_przedmiot_w_klasie']; ?>">
                                <?php echo $p['nazwa']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <p class="hint">Przejdź do:</p>
                    <button type="submit" name="akcja" value="temat" class="btn-action btn-tematy">Tematy i Obecność</button>
                    <button type="submit" name="akcja" value="oceny" class="btn-action btn-oceny">Dziennik Ocen</button>
                
                <?php else: ?>
                    <div style="color: red; padding: 10px; border: 1px solid red; border-radius: 5px;">
                        Brak przypisanych przedmiotów w tej klasie.
                    </div>
                <?php endif; ?>

            <?php endif; ?>

        </form>
    </div>

</body>
</html>