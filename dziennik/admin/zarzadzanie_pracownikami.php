<?php
session_start();
require_once '../db.php';

// Zabezpieczenie: tylko admin
if (!isset($_SESSION['rola']) || $_SESSION['rola'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$komunikat = "";
$typ_komunikatu = ""; // success / error
$wybrany_pracownik = null;

// OBSŁUGA AKCJI 

//  Zmiana uprawnień 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['zmien_role'])) {
    $id = $_POST['id_uzytkownika'];
    $nowa_rola = $_POST['rola'];
    
    
    if (in_array($nowa_rola, ['nauczyciel', 'sekretariat', 'admin'])) {
        try {
            // Jeśli zmieniamy na nauczyciela, trzeba sprawdzić czy ma wpis w tabeli Nauczyciel
            $conn->beginTransaction();

            $stmt = $conn->prepare("UPDATE Uzytkownik SET rola = :rola WHERE id_uzytkownika = :id");
            $stmt->bindParam(':rola', $nowa_rola);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

           
            if ($nowa_rola == 'nauczyciel') {
                $check = $conn->prepare("SELECT id_nauczyciela FROM Nauczyciel WHERE id_uzytkownika = :id");
                $check->bindParam(':id', $id);
                $check->execute();
                if ($check->rowCount() == 0) {
                    $ins = $conn->prepare("INSERT INTO Nauczyciel (id_uzytkownika) VALUES (:id)");
                    $ins->bindParam(':id', $id);
                    $ins->execute();
                }
            }

            $conn->commit();
            $komunikat = "Zmieniono uprawnienia pracownika.";
            $typ_komunikatu = "success";
        } catch (PDOException $e) {
            $conn->rollBack();
            $komunikat = "Błąd: " . $e->getMessage();
            $typ_komunikatu = "error";
        }
    }
}

//  Resetowanie hasła
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['zresetuj_haslo'])) {
    $id = $_POST['id_uzytkownika'];
    $haslo1 = $_POST['haslo1'];
    $haslo2 = $_POST['haslo2'];

    if ($haslo1 === $haslo2 && !empty($haslo1)) {
        try {
            $stmt = $conn->prepare("UPDATE Uzytkownik SET haslo = :haslo WHERE id_uzytkownika = :id");
            $stmt->bindParam(':haslo', $haslo1);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $komunikat = "Hasło zostało zmienione.";
            $typ_komunikatu = "success";
        } catch (PDOException $e) {
            $komunikat = "Błąd: " . $e->getMessage();
            $typ_komunikatu = "error";
        }
    } else {
        $komunikat = "Hasła nie są identyczne lub są puste.";
        $typ_komunikatu = "error";
    }
}

// Usuwanie konta
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['usun_konto'])) {
    $id = $_POST['id_uzytkownika'];
    
    // Zabezpieczenie przed usunięciem samego siebie
    if ($id == $_SESSION['user_id']) {
        $komunikat = "Nie możesz usunąć własnego konta administratora, gdy jesteś zalogowany!";
        $typ_komunikatu = "error";
    } else {
        try {
            $stmt = $conn->prepare("DELETE FROM Uzytkownik WHERE id_uzytkownika = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $komunikat = "Konto pracownika zostało usunięte.";
            $typ_komunikatu = "success";
            $_GET['id_pracownika'] = null; // Reset wyboru
        } catch (PDOException $e) {
            $komunikat = "Błąd: " . $e->getMessage();
            $typ_komunikatu = "error";
        }
    }
}


// Pobieramy tylko role: nauczyciel, sekretariat, admin
$sql_lista = "SELECT id_uzytkownika, imie, nazwisko, rola, login FROM Uzytkownik 
              WHERE rola IN ('nauczyciel', 'sekretariat', 'admin') 
              ORDER BY nazwisko ASC";
$pracownicy = $conn->query($sql_lista)->fetchAll(PDO::FETCH_ASSOC);

// POBRANIE WYBRANEGO PRACOWNIKA
$id_edycji = isset($_GET['id_pracownika']) ? $_GET['id_pracownika'] : (isset($_POST['id_uzytkownika']) ? $_POST['id_uzytkownika'] : null);

if ($id_edycji) {
    // Sprawdzamy czy ten ID faktycznie jest pracownikiem 
    $stmt = $conn->prepare("SELECT * FROM Uzytkownik WHERE id_uzytkownika = :id AND rola IN ('nauczyciel', 'sekretariat', 'admin')");
    $stmt->bindParam(':id', $id_edycji);
    $stmt->execute();
    $wybrany_pracownik = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Jeśli nie znaleziono  pokaż błąd
    if (!$wybrany_pracownik && $id_edycji) {
         $komunikat = "Nie znaleziono pracownika o podanym ID.";
         $typ_komunikatu = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Konta pracowników</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #fff; padding: 20px; text-align: center; }
        
        .top-bar { text-align: left; margin-bottom: 20px; }
        .btn-powrot { padding: 8px 20px; border: 1px solid #000; background: white; text-decoration: none; color: black; border-radius: 5px; }
        
        h1 { margin-bottom: 40px; font-weight: normal; font-size: 32px; }

        
        .select-container {
            margin-bottom: 40px;
        }
        .select-label { font-size: 18px; margin-right: 10px; }
        select { padding: 5px; font-size: 16px; border: 2px solid #000; border-radius: 5px; width: 300px; background: #eee; }

        
        .edit-container { display: flex; justify-content: center; gap: 40px; margin-top: 40px; max-width: 1000px; margin-left: auto; margin-right: auto; }
        .edit-column { flex: 1; text-align: left; padding: 20px; }
        .separator { width: 1px; background-color: #000; }
        .column-title { text-align: center; font-size: 18px; margin-bottom: 20px; }
        
        input[type="password"] { width: 100%; padding: 5px; margin-bottom: 10px; border: 2px solid #000; border-radius: 5px; }
        
        .radio-group label { display: block; margin-bottom: 10px; font-size: 18px; }
        
        .btn-zapisz { display: block; width: 100%; padding: 10px; background: #ddd; border: 2px solid #000; border-radius: 5px; font-size: 18px; cursor: pointer; margin-top: 20px; }
        .btn-usun { border: 2px solid #000; }
    </style>
</head>
<body>

    <div class="top-bar">
        <a href="admin.php" class="btn-powrot">Panel główny</a>
    </div>

    <h1>Konta pracowników</h1>

    <?php if ($komunikat): ?>
        <p style="color: <?php echo $typ_komunikatu == 'success' ? 'green' : 'red'; ?>; font-weight: bold; margin-bottom: 20px;"><?php echo $komunikat; ?></p>
    <?php endif; ?>

    <div class="select-container">
        <form method="GET">
            <label class="select-label">Wybierz konto:</label>
            <select name="id_pracownika" onchange="this.form.submit()">
                <option value="">-- Wybierz pracownika --</option>
                <?php foreach ($pracownicy as $p): ?>
                    <option value="<?php echo $p['id_uzytkownika']; ?>" <?php if($wybrany_pracownik && $wybrany_pracownik['id_uzytkownika'] == $p['id_uzytkownika']) echo 'selected'; ?>>
                        <?php echo $p['imie'] . ' ' . $p['nazwisko'] . ' (' . ucfirst($p['rola']) . ')'; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if ($wybrany_pracownik): ?>

        <div class="edit-container">
            
            <div class="edit-column">
                <div class="column-title">Edytuj uprawnienia</div>
                <form method="POST">
                    <input type="hidden" name="id_uzytkownika" value="<?php echo $wybrany_pracownik['id_uzytkownika']; ?>">
                    <div class="radio-group">
                        <label><input type="radio" name="rola" value="nauczyciel" <?php if($wybrany_pracownik['rola']=='nauczyciel') echo 'checked'; ?>> Nauczyciel</label>
                        <label><input type="radio" name="rola" value="sekretariat" <?php if($wybrany_pracownik['rola']=='sekretariat') echo 'checked'; ?>> Sekretariat</label>
                        <label><input type="radio" name="rola" value="admin" <?php if($wybrany_pracownik['rola']=='admin') echo 'checked'; ?>> Dyrektor (Admin)</label>
                    </div>
                    <button type="submit" name="zmien_role" class="btn-zapisz">Zapisz</button>
                </form>
            </div>

            <div class="separator"></div>

            <div class="edit-column">
                <div class="column-title">Zmień hasło</div>
                <form method="POST">
                    <input type="hidden" name="id_uzytkownika" value="<?php echo $wybrany_pracownik['id_uzytkownika']; ?>">
                    <label style="font-size: 14px;">Nowe hasło:</label>
                    <input type="password" name="haslo1" required>
                    <label style="font-size: 14px;">Powtórz hasło:</label>
                    <input type="password" name="haslo2" required>
                    <button type="submit" name="zresetuj_haslo" class="btn-zapisz">Zapisz</button>
                </form>
            </div>

            <div class="separator"></div>

            <div class="edit-column">
                <div class="column-title">Usuń konto</div>
                <form method="POST" onsubmit="return confirm('Czy na pewno trwale usunąć konto tego pracownika?');">
                    <input type="hidden" name="id_uzytkownika" value="<?php echo $wybrany_pracownik['id_uzytkownika']; ?>">
                    <div style="height: 80px;"></div>
                    <button type="submit" name="usun_konto" class="btn-zapisz btn-usun">Usuń konto</button>
                </form>
            </div>

        </div>

    <?php endif; ?>

</body>
</html>