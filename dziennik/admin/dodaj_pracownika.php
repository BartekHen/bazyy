<?php
session_start();
require_once '../db.php';

// Zabezpieczenie
if (!isset($_SESSION['rola']) || $_SESSION['rola'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pobieramy dane z formularza
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];
    $email = $_POST['email']; 
    $rola = $_POST['rola'];
    
    
    $login = strtolower(substr($imie, 0, 1) . $nazwisko);
    
    //Gemerowanie hasła
    $haslo = "szkola123"; 

    try {
        $conn->beginTransaction(); 

        
        $stmt = $conn->prepare("INSERT INTO Uzytkownik (login, haslo, imie, nazwisko, email, rola, telefon) VALUES (:login, :haslo, :imie, :nazwisko, :email, :rola, :telefon)");
        $stmt->bindParam(':login', $login);
        $stmt->bindParam(':haslo', $haslo);
        $stmt->bindParam(':imie', $imie);
        $stmt->bindParam(':nazwisko', $nazwisko);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':rola', $rola);
        $stmt->bindParam(':telefon', $telefon);
        $stmt->execute();
        
        // Pobieramy ID nowo dodanego użytkownika
        $last_id = $conn->lastInsertId();

        // Jeśli to Nauczyciel, dodajemy wpis do tabeli Nauczyciel
        if ($rola == 'nauczyciel') {
            $stmt_nauczyciel = $conn->prepare("INSERT INTO Nauczyciel (id_uzytkownika) VALUES (:id)");
            $stmt_nauczyciel->bindParam(':id', $last_id);
            $stmt_nauczyciel->execute();
        }
   

        $conn->commit(); // Zatwierdzamy zmiany
        $message = "Pomyślnie dodano pracownika! Login: <b>$login</b>, Hasło: <b>$haslo</b>";

    } catch (PDOException $e) {
        $conn->rollBack();
        
        if ($e->getCode() == 23000) {
            $message = "Błąd: Taki login lub email już istnieje w bazie!";
        } else {
            $message = "Błąd bazy danych: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Dodaj pracownika</title>
    <style>
        
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f4f4f4; }
        .form-container { background: white; padding: 40px; border: 1px solid #ccc; width: 400px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; margin-bottom: 30px; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        input[type="text"], input[type="email"] { width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box; }
        
        .radio-group { margin-top: 20px; }
        .radio-group label { display: inline; font-weight: normal; margin-right: 15px; }
        
        .btn-save { display: block; width: 100%; padding: 10px; background-color: #eee; border: 1px solid #999; margin-top: 30px; cursor: pointer; font-size: 16px; }
        .btn-save:hover { background-color: #ddd; }
        
        .btn-back { position: absolute; top: 20px; left: 20px; padding: 10px 20px; text-decoration: none; background: #fff; border: 1px solid #ccc; color: black; }
        
        .success { color: green; text-align: center; margin-bottom: 15px; }
        .error { color: red; text-align: center; margin-bottom: 15px; }
    </style>
</head>
<body>

    <a href="admin.php" class="btn-back">Panel główny</a>

    <div class="form-container">
        <h2>Dodaj pracownika</h2>
        
        <?php if ($message): ?>
            <div class="<?php echo strpos($message, 'Błąd') !== false ? 'error' : 'success'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Imię:</label>
            <input type="text" name="imie" required>

            <label>Nazwisko:</label>
            <input type="text" name="nazwisko" required>

            <label>Adres e-mail:</label>
            <input type="email" name="email" required>

            <label>Telefon:</label>
            <input type="text" name="telefon">

            <div class="radio-group">
                <label>Rola:</label><br>
                <input type="radio" name="rola" value="nauczyciel" checked> Nauczyciel<br>
                <input type="radio" name="rola" value="sekretariat"> Sekretariat<br>
                <input type="radio" name="rola" value="admin"> Dyrektor (Admin)
            </div>

            <button type="submit" class="btn-save">Zapisz</button>
        </form>
    </div>

</body>
</html>