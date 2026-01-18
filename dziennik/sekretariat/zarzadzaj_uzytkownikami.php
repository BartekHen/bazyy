<?php
session_start();
require_once '../db.php';

// ZMIANA: Zabezpieczenie dla SEKRETARIATU
if (!isset($_SESSION['rola']) || $_SESSION['rola'] !== 'sekretariat') {
    header("Location: ../index.php");
    exit;
}

$komunikat = "";
$typ_komunikatu = ""; // success / error
$wybrany_uzytkownik = null;
$szukano = false; 

// OBSŁUGA AKCJI 

// Zmiana roli (Zablokowana dla pracowników)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['zmien_role'])) {
    $id = $_POST['id_uzytkownika'];
    $nowa_rola = $_POST['rola'];
    
    // Sekretariat może zmieniać tylko na ucznia lub rodzica
    if (in_array($nowa_rola, ['uczen', 'rodzic'])) {
        try {
            // Dodatkowe zabezpieczenie w UPDATE: nie ruszamy adminów/nauczycieli
            $stmt = $conn->prepare("UPDATE Uzytkownik SET rola = :rola WHERE id_uzytkownika = :id AND rola IN ('uczen', 'rodzic')");
            $stmt->bindParam(':rola', $nowa_rola);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $komunikat = "Zmieniono rolę użytkownika.";
                $typ_komunikatu = "success";
            } else {
                $komunikat = "Nie można zmienić roli tego użytkownika.";
                $typ_komunikatu = "error";
            }
        } catch (PDOException $e) {
            $komunikat = "Błąd: " . $e->getMessage();
            $typ_komunikatu = "error";
        }
    }
}

// Resetowanie hasła
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['zresetuj_haslo'])) {
    $id = $_POST['id_uzytkownika'];
    $haslo1 = $_POST['haslo1'];
    $haslo2 = $_POST['haslo2'];

    if ($haslo1 === $haslo2 && !empty($haslo1)) {
        try {
            // Zabezpieczenie: sekretariat nie może zmienić hasła admina
            $stmt = $conn->prepare("UPDATE Uzytkownik SET haslo = :haslo WHERE id_uzytkownika = :id AND rola IN ('uczen', 'rodzic')");
            $stmt->bindParam(':haslo', $haslo1);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $komunikat = "Hasło zostało zmienione.";
                $typ_komunikatu = "success";
            } else {
                $komunikat = "Brak uprawnień do zmiany hasła tego użytkownika.";
                $typ_komunikatu = "error";
            }
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
    try {
        // Zabezpieczenie: tylko uczniowie/rodzice
        $stmt = $conn->prepare("DELETE FROM Uzytkownik WHERE id_uzytkownika = :id AND rola IN ('uczen', 'rodzic')");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $komunikat = "Konto zostało usunięte.";
            $typ_komunikatu = "success";
            $_GET['id_edycji'] = null;
        } else {
            $komunikat = "Nie można usunąć tego konta (brak uprawnień).";
            $typ_komunikatu = "error";
        }
    } catch (PDOException $e) {
        $komunikat = "Błąd: " . $e->getMessage();
        $typ_komunikatu = "error";
    }
}

// WYSZUKIWANIE I POBIERANIE DANYCH 

$wyniki_wyszukiwania = [];

if (isset($_GET['szukaj_btn'])) {
    $szukano = true; 
    $imie = $_GET['szukaj_imie'];
    $nazwisko = $_GET['szukaj_nazwisko'];
    
    // Szukamy TYLKO wśród uczniów i rodziców (sekretariat nie widzi w szukajce nauczycieli)
    $sql = "SELECT * FROM Uzytkownik WHERE rola IN ('uczen', 'rodzic')";
    
    if (!empty($imie)) $sql .= " AND imie LIKE :imie";
    if (!empty($nazwisko)) $sql .= " AND nazwisko LIKE :nazwisko";
    
    $stmt = $conn->prepare($sql);
    if (!empty($imie)) { $imie_param = "%$imie%"; $stmt->bindParam(':imie', $imie_param); }
    if (!empty($nazwisko)) { $nazwisko_param = "%$nazwisko%"; $stmt->bindParam(':nazwisko', $nazwisko_param); }
    $stmt->execute();
    $wyniki_wyszukiwania = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Pobranie konkretnego użytkownika do edycji
$id_edycji = isset($_GET['id_edycji']) ? $_GET['id_edycji'] : (isset($_POST['id_uzytkownika']) ? $_POST['id_uzytkownika'] : null);

if ($id_edycji) {
    // Dodatkowe zabezpieczenie przy pobieraniu
    $stmt = $conn->prepare("SELECT * FROM Uzytkownik WHERE id_uzytkownika = :id AND rola IN ('uczen', 'rodzic')");
    $stmt->bindParam(':id', $id_edycji);
    $stmt->execute();
    $wybrany_uzytkownik = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Zarządzanie Użytkownikami (Sekretariat)</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #fff; padding: 20px; text-align: center; }
        
        .top-bar { text-align: left; margin-bottom: 20px; }
        .btn-powrot { padding: 8px 20px; border: 1px solid #000; background: white; text-decoration: none; color: black; border-radius: 5px; }
        
        h1 { margin-bottom: 40px; font-weight: normal; font-size: 32px; }
        
        .search-container { display: inline-block; border: 1px solid #000; padding: 10px; margin-bottom: 30px; background: #fff; }
        .search-inputs { display: flex; gap: 10px; align-items: center; justify-content: center; background: #ddd; padding: 10px; border: 1px solid #999; }
        .search-inputs input { padding: 5px; border: 2px solid #000; border-radius: 5px; width: 150px; font-family: Arial, sans-serif; }
        .btn-szukaj { margin-top: 10px; padding: 5px 30px; background: #ddd; border: 1px solid #000; border-radius: 5px; cursor: pointer; font-weight: bold; }

        .edit-container { display: flex; justify-content: center; gap: 40px; margin-top: 40px; max-width: 1000px; margin-left: auto; margin-right: auto; }
        .edit-column { flex: 1; text-align: left; padding: 20px; }
        .separator { width: 1px; background-color: #000; }
        .column-title { text-align: center; font-size: 18px; margin-bottom: 20px; }
        
        input[type="password"] { width: 100%; padding: 5px; margin-bottom: 10px; border: 2px solid #000; border-radius: 5px; }
        .radio-group label { display: block; margin-bottom: 10px; font-size: 18px; }
        .btn-zapisz { display: block; width: 100%; padding: 10px; background: #ddd; border: 2px solid #000; border-radius: 5px; font-size: 18px; cursor: pointer; margin-top: 20px; }
        .btn-usun { border: 2px solid #000; }

        .results-list { margin-top: 20px; text-align: left; display: inline-block; }
        .results-list a { display: block; padding: 5px; color: blue; text-decoration: underline; }
        .results-list span { color: #555; font-size: 0.9em; margin-left: 10px; }

        .search-error { color: red; font-weight: bold; margin-top: 15px; }
    </style>
</head>
<body>

    <div class="top-bar">
        <a href="sekretariat.php" class="btn-powrot">Panel główny</a>
    </div>

    <h1>Edycja kont (Uczniowie i Rodzice)</h1>

    <?php if ($komunikat): ?>
        <p style="color: <?php echo $typ_komunikatu == 'success' ? 'green' : 'red'; ?>; font-weight: bold;"><?php echo $komunikat; ?></p>
    <?php endif; ?>

    <div style="text-align: left; max-width: 800px; margin: 0 auto;">
        <p>Wyszukaj użytkownika:</p>
        <form method="GET">
            <div class="search-inputs">
                <input type="text" name="szukaj_imie" placeholder="Imię" value="<?php echo isset($_GET['szukaj_imie']) ? htmlspecialchars($_GET['szukaj_imie']) : ''; ?>">
                <input type="text" name="szukaj_nazwisko" placeholder="Nazwisko" value="<?php echo isset($_GET['szukaj_nazwisko']) ? htmlspecialchars($_GET['szukaj_nazwisko']) : ''; ?>">
            </div>
            <div style="text-align: center;">
                <button type="submit" name="szukaj_btn" class="btn-szukaj">Szukaj</button>
            </div>
        </form>

        <?php if ($szukano): ?>
            <?php if (!empty($wyniki_wyszukiwania)): ?>
                <div class="results-list">
                    <p>Znaleziono:</p>
                    <?php foreach ($wyniki_wyszukiwania as $w): ?>
                        <a href="?id_edycji=<?php echo $w['id_uzytkownika']; ?>&szukaj_imie=<?php echo $_GET['szukaj_imie']; ?>&szukaj_nazwisko=<?php echo $_GET['szukaj_nazwisko']; ?>&szukaj_btn=">
                            <?php echo $w['imie'] . " " . $w['nazwisko']; ?>
                            <span>(<?php echo $w['rola']; ?>, login: <?php echo $w['login']; ?>)</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php elseif (!$wybrany_uzytkownik): ?>
                <div class="search-error">Nie znaleziono użytkownika (lub brak uprawnień).</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php if ($wybrany_uzytkownik): ?>
        
        <h2 style="margin-top: 40px;">Edytujesz: <?php echo $wybrany_uzytkownik['imie'] . " " . $wybrany_uzytkownik['nazwisko']; ?></h2>

        <div class="edit-container">
            
            <div class="edit-column">
                <div class="column-title">Zmień rolę</div>
                <form method="POST">
                    <input type="hidden" name="id_uzytkownika" value="<?php echo $wybrany_uzytkownik['id_uzytkownika']; ?>">
                    <div class="radio-group">
                        <label><input type="radio" name="rola" value="rodzic" <?php if($wybrany_uzytkownik['rola']=='rodzic') echo 'checked'; ?>> Rodzic</label>
                        <label><input type="radio" name="rola" value="uczen" <?php if($wybrany_uzytkownik['rola']=='uczen') echo 'checked'; ?>> Uczeń</label>
                    </div>
                    <button type="submit" name="zmien_role" class="btn-zapisz">Zapisz</button>
                </form>
            </div>

            <div class="separator"></div>

            <div class="edit-column">
                <div class="column-title">Resetowanie hasła</div>
                <form method="POST">
                    <input type="hidden" name="id_uzytkownika" value="<?php echo $wybrany_uzytkownik['id_uzytkownika']; ?>">
                    <label style="font-size: 14px;">Nowe hasło:</label>
                    <input type="password" name="haslo1" required>
                    <label style="font-size: 14px;">Powtórz:</label>
                    <input type="password" name="haslo2" required>
                    <button type="submit" name="zresetuj_haslo" class="btn-zapisz">Zapisz</button>
                </form>
            </div>

            <div class="separator"></div>

            <div class="edit-column">
                <div class="column-title">Usuń konto</div>
                <form method="POST" onsubmit="return confirm('Czy na pewno usunąć to konto?');">
                    <input type="hidden" name="id_uzytkownika" value="<?php echo $wybrany_uzytkownik['id_uzytkownika']; ?>">
                    <div style="height: 80px;"></div>
                    <button type="submit" name="usun_konto" class="btn-zapisz btn-usun">Usuń konto</button>
                </form>
            </div>

        </div>

    <?php endif; ?>

</body>
</html>