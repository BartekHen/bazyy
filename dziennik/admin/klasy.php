<?php
session_start();
require_once '../db.php';

// Zabezpieczenie: tylko admin
if (!isset($_SESSION['rola']) || $_SESSION['rola'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$komunikat = "";
$id_aktywnej_klasy = isset($_GET['id_klasy']) ? $_GET['id_klasy'] : null;

// TWORZENIE NOWEJ KLASY 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nowa_klasa'])) {
    $nazwa = $_POST['nazwa_klasy'];
    $stopien = (int)filter_var($nazwa, FILTER_SANITIZE_NUMBER_INT); 
    if($stopien == 0) $stopien = 1; 

    try {
        $stmt = $conn->prepare("INSERT INTO Klasa (nazwa, stopien) VALUES (:nazwa, :stopien)");
        $stmt->execute([':nazwa' => $nazwa, ':stopien' => $stopien]);
        $id_aktywnej_klasy = $conn->lastInsertId();
        $komunikat = "Utworzono klasę $nazwa.";
    } catch (PDOException $e) {
        $komunikat = "Błąd: " . $e->getMessage();
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_klasy_hidden'])) {
    $id_aktywnej_klasy = $_POST['id_klasy_hidden'];
}

// --- DODAWANIE PRZEDMIOTU
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['dodaj_przydzial'])) {
    $id_przedmiotu = $_POST['id_przedmiotu'];
    $id_nauczyciela = $_POST['id_nauczyciela'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO Przedmiot_w_klasie (id_klasy, id_przedmiotu, id_nauczyciela) VALUES (:k, :p, :n)");
        $stmt->execute([':k' => $id_aktywnej_klasy, ':p' => $id_przedmiotu, ':n' => $id_nauczyciela]);
        $komunikat = "Dodano przedmiot do klasy.";
    } catch (PDOException $e) {
        $komunikat = "Błąd: " . $e->getMessage();
    }
}

// DODAWANIE UCZNIA
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['dodaj_ucznia'])) {
    $imie = trim($_POST['imie_ucznia']);
    $nazwisko = trim($_POST['nazwisko_ucznia']);
    
    try {
        //  Znajdź ID użytkownika
        $stmt_find = $conn->prepare("SELECT id_uzytkownika FROM Uzytkownik WHERE imie = :imie AND nazwisko = :nazwisko AND rola = 'uczen'");
        $stmt_find->execute([':imie' => $imie, ':nazwisko' => $nazwisko]);
        $user = $stmt_find->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $uid = $user['id_uzytkownika'];
            
            // Sprawdź status tego ucznia w tabeli Uczen
            $stmt_chk = $conn->prepare("SELECT id_klasy, id_ucznia FROM Uczen WHERE id_uzytkownika = :uid");
            $stmt_chk->execute([':uid' => $uid]);
            $dane_ucznia = $stmt_chk->fetch(PDO::FETCH_ASSOC);

            // Obliczamy kolejny numer w dzienniku dla TEJ klasy
            $stmt_nr = $conn->prepare("SELECT COALESCE(MAX(nr_dziennika), 0) + 1 FROM Uczen WHERE id_klasy = :kid");
            $stmt_nr->execute([':kid' => $id_aktywnej_klasy]);
            $nr = $stmt_nr->fetchColumn();

            if ($dane_ucznia) {
                
                if ($dane_ucznia['id_klasy'] !== null) {
                    // Ma już klasę -> Błąd
                    if($dane_ucznia['id_klasy'] == $id_aktywnej_klasy) {
                        $komunikat = "Ten uczeń jest już w tej klasie.";
                    } else {
                        $komunikat = "Błąd: Ten uczeń jest już przypisany do innej klasy!";
                    }
                } else {
                    $stmt_upd = $conn->prepare("UPDATE Uczen SET id_klasy = :kid, nr_dziennika = :nr WHERE id_ucznia = :id_ucznia");
                    $stmt_upd->execute([':kid' => $id_aktywnej_klasy, ':nr' => $nr, ':id_ucznia' => $dane_ucznia['id_ucznia']]);
                    $komunikat = "Przypisano zaimportowanego ucznia: $imie $nazwisko";
                }
            } else {
                $stmt_ins = $conn->prepare("INSERT INTO Uczen (id_uzytkownika, id_klasy, nr_dziennika) VALUES (:uid, :kid, :nr)");
                $stmt_ins->execute([':uid' => $uid, ':kid' => $id_aktywnej_klasy, ':nr' => $nr]);
                $komunikat = "Utworzono i przypisano ucznia: $imie $nazwisko";
            }

        } else {
            $komunikat = "Nie znaleziono ucznia w bazie użytkowników. Upewnij się, że piszesz poprawnie imię i nazwisko.";
        }
    } catch (PDOException $e) {
        $komunikat = "Błąd: " . $e->getMessage();
    }
}
// APISYWANIE PLANU LEKCJI 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['zapisz_plan'])) {
    
    $plan_dane = isset($_POST['plan']) ? $_POST['plan'] : [];
    $zajete_sloty = []; // Tablica pomocnicza do sprawdzania duplikatów
    $kolizja = false;   // Flaga błędu

    //Sprawdzanie Kolizji
    foreach ($plan_dane as $id_pwk => $terminy_przedmiotu) {
        foreach ($terminy_przedmiotu as $slot) {
            
            if (in_array($slot, $zajete_sloty)) {
                $kolizja = $slot; // Zapisujemy, który to slot
                break 2; // Przerywamy obie pętle
            }
            $zajete_sloty[] = $slot; // Dodajemy slot do zajętych
        }
    }

    if ($kolizja) {
        // Jeśli znaleziono kolizję, nie zapisujemy, tylko wyświetlamy błąd
        list($d, $h) = explode('_', $kolizja);
        $dni_nazwy = [1 => 'Poniedziałek', 2 => 'Wtorek', 3 => 'Środa', 4 => 'Czwartek', 5 => 'Piątek'];
        
        $komunikat = "<span style='color:red'>BŁĄD ZAPISU: Wykryto kolizję! Masz dwa przedmioty w terminie: <strong>" . $dni_nazwy[$d] . ", lekcja " . $h . "</strong>. Popraw plan.</span>";
    
    } else {
        
        try {
            $conn->beginTransaction();
            
            $stmt_ids = $conn->prepare("SELECT id_przedmiot_w_klasie FROM Przedmiot_w_klasie WHERE id_klasy = :id");
            $stmt_ids->execute([':id' => $id_aktywnej_klasy]);
            
            while($row = $stmt_ids->fetch(PDO::FETCH_ASSOC)) {
                $id_pwk = $row['id_przedmiot_w_klasie'];
                $string_terminy = isset($plan_dane[$id_pwk]) ? implode(',', $plan_dane[$id_pwk]) : null;
                
                $stmt_upd = $conn->prepare("UPDATE Przedmiot_w_klasie SET terminy = :t WHERE id_przedmiot_w_klasie = :id");
                $stmt_upd->execute([':t' => $string_terminy, ':id' => $id_pwk]);
            }
            $conn->commit();
            $komunikat = "Zapisano plan lekcji!";
        } catch (PDOException $e) {
            $conn->rollBack();
            $komunikat = "Błąd zapisu planu: " . $e->getMessage();
        }
    }
}


// POBIERANIE DANYCH DO WIDOKU 
$klasy_lista = $conn->query("SELECT * FROM Klasa")->fetchAll(PDO::FETCH_ASSOC);
$nauczyciele = $conn->query("SELECT n.id_nauczyciela, u.imie, u.nazwisko FROM Nauczyciel n JOIN Uzytkownik u ON n.id_uzytkownika = u.id_uzytkownika")->fetchAll(PDO::FETCH_ASSOC);
$przedmioty_baza = $conn->query("SELECT * FROM Przedmiot")->fetchAll(PDO::FETCH_ASSOC);

$przydzialy = [];
$uczniowie = [];

if ($id_aktywnej_klasy) {
    // Lista przedmiotów w klasie 
    $sql = "SELECT pwk.id_przedmiot_w_klasie, pwk.terminy, p.nazwa as przedmiot, u.imie, u.nazwisko 
            FROM Przedmiot_w_klasie pwk 
            JOIN Przedmiot p ON pwk.id_przedmiotu = p.id_przedmiotu
            JOIN Nauczyciel n ON pwk.id_nauczyciela = n.id_nauczyciela
            JOIN Uzytkownik u ON n.id_uzytkownika = u.id_uzytkownika
            WHERE pwk.id_klasy = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id_aktywnej_klasy]);
    $przydzialy = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lista uczniów
    $sql_uczniowie = "SELECT u.imie, u.nazwisko, ucz.nr_dziennika 
                      FROM Uczen ucz
                      JOIN Uzytkownik u ON ucz.id_uzytkownika = u.id_uzytkownika
                      WHERE ucz.id_klasy = :id ORDER BY ucz.nr_dziennika";
    $stmt_u = $conn->prepare($sql_uczniowie);
    $stmt_u->execute([':id' => $id_aktywnej_klasy]);
    $uczniowie = $stmt_u->fetchAll(PDO::FETCH_ASSOC);
}

// Konfiguracja planu
$dni = [1 => 'Pn', 2 => 'Wt', 3 => 'Śr', 4 => 'Cz', 5 => 'Pt'];
$godziny = [1, 2, 3, 4, 5, 6, 7, 8];
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Konfiguracja Klasy</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .top-bar { margin-bottom: 20px; }
        .btn-powrot { padding: 8px 20px; border: 1px solid #000; background: white; text-decoration: none; color: black; border-radius: 5px; }
        
        .container { max-width: 1200px; margin: 0 auto; }
        
        .section-card { 
            background: white; padding: 20px; margin-bottom: 30px; 
            border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
        }
        
        h2 { margin-top: 0; color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background-color: #eee; padding: 10px; text-align: left; }
        td { border-bottom: 1px solid #ddd; padding: 10px; }
        
        .plan-grid { display: grid; grid-template-columns: 200px repeat(5, 1fr); gap: 5px; margin-top: 20px; font-size: 14px; }
        .pg-header { font-weight: bold; background: #00bcd4; color: white; padding: 10px; text-align: center; border-radius: 3px; }
        .pg-row-label { font-weight: bold; background: #f9f9f9; padding: 10px; display: flex; flex-direction: column; justify-content: center; border-bottom: 1px solid #eee; }
        .pg-col { background: #fff; border: 1px solid #eee; padding: 5px; }
        
        .hour-chk { display: block; margin-bottom: 2px; cursor: pointer; }
        .hour-chk:hover { background-color: #e0f7fa; }
        
        .btn-action { background: #4CAF50; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px; font-weight: bold; }
        .btn-action:hover { background: #45a049; }
        
        select, input[type="text"] { padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
    </style>
</head>
<body>

<div class="container">
    <div class="top-bar">
        <a href="admin.php" class="btn-powrot">← Powrót</a>
    </div>

    <h1 style="text-align:center;">Konfiguracja Klasy</h1>

    <?php if ($komunikat): ?>
        <div style="text-align:center; color: green; font-weight:bold; margin-bottom:20px; padding:10px; background:#e8f5e9; border-radius:5px;">
            <?php echo $komunikat; ?>
        </div>
    <?php endif; ?>

    <div class="section-card" style="text-align: center;">
        <form method="GET" style="display:inline-block; margin-right: 30px;">
            <label>Edytuj klasę:</label>
            <select name="id_klasy" onchange="this.form.submit()" style="padding: 10px; width: 150px;">
                <option value="">-- Wybierz --</option>
                <?php foreach ($klasy_lista as $k): ?>
                    <option value="<?php echo $k['id_klasy']; ?>" <?php if($id_aktywnej_klasy == $k['id_klasy']) echo 'selected'; ?>>
                        <?php echo $k['nazwa']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <form method="POST" style="display:inline-block; border-left: 2px solid #eee; padding-left: 30px;">
            <label>Nowa klasa:</label>
            <input type="text" name="nazwa_klasy" placeholder="np. 1A" size="5" required>
            <button type="submit" name="nowa_klasa" class="btn-action" style="background:#2196F3;">Utwórz</button>
        </form>
    </div>

    <?php if ($id_aktywnej_klasy): ?>
    
        <div class="section-card">
            <h2>Przedmioty i Plan Lekcji</h2>
            <p style="color:#666; font-size:14px;">1. Dodaj przedmiot do klasy. | 2. Zaznacz w tabeli kiedy się odbywa. | 3. Kliknij "Zapisz Plan".</p>
            
            <form method="POST" style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <input type="hidden" name="id_klasy_hidden" value="<?php echo $id_aktywnej_klasy; ?>">
                <strong>Dodaj przedmiot: </strong>
                <select name="id_przedmiotu" required>
                    <option value="">-- Przedmiot --</option>
                    <?php foreach ($przedmioty_baza as $pb) echo "<option value='{$pb['id_przedmiotu']}'>{$pb['nazwa']}</option>"; ?>
                </select>
                <select name="id_nauczyciela" required>
                    <option value="">-- Nauczyciel --</option>
                    <?php foreach ($nauczyciele as $n) echo "<option value='{$n['id_nauczyciela']}'>{$n['imie']} {$n['nazwisko']}</option>"; ?>
                </select>
                <button type="submit" name="dodaj_przydzial" class="btn-action" style="padding: 6px 15px; font-size: 13px;">Dodaj</button>
            </form>

            <form method="POST">
                <input type="hidden" name="id_klasy_hidden" value="<?php echo $id_aktywnej_klasy; ?>">
                
                <div class="plan-grid">
                    <div class="pg-header">Przedmiot (Nauczyciel)</div>
                    <?php foreach($dni as $d) echo "<div class='pg-header'>$d</div>"; ?>

                    <?php foreach ($przydzialy as $p): ?>
                        <?php 
                            $id_pwk = $p['id_przedmiot_w_klasie'];
                            $zaznaczone = explode(',', $p['terminy'] ?? ''); 
                        ?>
                        <div class="pg-row-label">
                            <span style="color:#000; font-size:15px;"><?php echo $p['przedmiot']; ?></span>
                            <small style="color:#777;"><?php echo $p['imie'].' '.$p['nazwisko']; ?></small>
                        </div>

                        <?php foreach($dni as $nr_dnia => $skrot): ?>
                            <div class="pg-col">
                                <?php foreach($godziny as $g): ?>
                                    <?php 
                                        $klucz = $nr_dnia . '_' . $g; 
                                        $checked = in_array($klucz, $zaznaczone) ? 'checked' : '';
                                    ?>
                                    <label class="hour-chk">
                                        <input type="checkbox" name="plan[<?php echo $id_pwk; ?>][]" value="<?php echo $klucz; ?>" <?php echo $checked; ?>>
                                        <?php echo $g; ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>

                <div style="text-align: right; margin-top: 20px;">
                    <button type="submit" name="zapisz_plan" class="btn-action" style="font-size: 16px; padding: 12px 30px;">ZAPISZ CAŁY PLAN</button>
                </div>
            </form>
        </div>

        <div class="section-card">
            <h2>Uczniowie w klasie</h2>
            
            <table style="margin-bottom: 20px;">
                <thead><tr><th>Nr</th><th>Imię i Nazwisko</th></tr></thead>
                <tbody>
                    <?php if (count($uczniowie) > 0): ?>
                        <?php foreach ($uczniowie as $u): ?>
                        <tr><td><?php echo $u['nr_dziennika']; ?></td><td><?php echo $u['imie'].' '.$u['nazwisko']; ?></td></tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="2">Brak uczniów.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <form method="POST" style="background: #f9f9f9; padding: 15px; border-radius: 5px; display:flex; align-items:center; gap:10px;">
                <input type="hidden" name="id_klasy_hidden" value="<?php echo $id_aktywnej_klasy; ?>">
                <strong>Przypisz ucznia: </strong>
                <input type="text" name="imie_ucznia" placeholder="Imię" required>
                <input type="text" name="nazwisko_ucznia" placeholder="Nazwisko" required>
                <button type="submit" name="dodaj_ucznia" class="btn-action" style="padding: 6px 15px; font-size: 13px;">Przypisz</button>
            </form>
        </div>

    <?php endif; ?>
</div>

</body>
</html>