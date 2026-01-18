<?php
session_start();
require_once '../db.php';

// 1. Zabezpieczenie: tylko nauczyciel
if (!isset($_SESSION['rola']) || $_SESSION['rola'] !== 'nauczyciel') {
    header("Location: ../index.php");
    exit;
}

$id_nauczyciela_user = $_SESSION['user_id'];
$komunikat = "";

// Pobieramy ID nauczyciela z tabeli Nauczyciel (na podstawie ID usera)
$stmt = $conn->prepare("SELECT id_nauczyciela FROM Nauczyciel WHERE id_uzytkownika = :uid");
$stmt->execute([':uid' => $id_nauczyciela_user]);
$nauczyciel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$nauczyciel) die("Błąd: Nie znaleziono profilu nauczyciela.");
$id_nauczyciela = $nauczyciel['id_nauczyciela'];


// 2. Obsługa DODAWANIA sprawdzianu
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['dodaj_sprawdzian'])) {
    $id_pwk = $_POST['id_przedmiot_w_klasie'];
    $data = $_POST['data'];
    $typ = $_POST['typ'];
    $opis = trim($_POST['opis']);
    $blad_limitu = false;

    if (!empty($opis) && !empty($data)) {
        
        // --- NOWE ZABEZPIECZENIE: LIMIT 2 SPRAWDZIANÓW TYGODNIOWO ---
        if ($typ == 'Sprawdzian') {
            // A. Najpierw musimy dowiedzieć się, jaka to KLASA (bo limit dotyczy klasy, a nie przedmiotu)
            $stmt_klasa = $conn->prepare("SELECT id_klasy FROM Przedmiot_w_klasie WHERE id_przedmiot_w_klasie = :id");
            $stmt_klasa->execute([':id' => $id_pwk]);
            $id_klasy = $stmt_klasa->fetchColumn();

            // B. Liczymy ile ta klasa ma już sprawdzianów w tym samym tygodniu co nowa data
            // Funkcja SQL YEARWEEK(data, 1) grupuje daty po tygodniach (tryb 1 = tydzień zaczyna się w poniedziałek)
            $sql_check = "SELECT COUNT(*) 
                          FROM Sprawdzian s
                          JOIN Przedmiot_w_klasie pwk ON s.id_przedmiot_w_klasie = pwk.id_przedmiot_w_klasie
                          WHERE pwk.id_klasy = :kid 
                          AND s.typ = 'Sprawdzian' 
                          AND YEARWEEK(s.data, 1) = YEARWEEK(:nowa_data, 1)";
            
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->execute([':kid' => $id_klasy, ':nowa_data' => $data]);
            $liczba_sprawdzianow = $stmt_check->fetchColumn();

            if ($liczba_sprawdzianow >= 2) {
                $blad_limitu = true;
                $komunikat = "<span style='color:red; font-weight:bold;'>BŁĄD: Ta klasa ma już 2 sprawdziany w tym tygodniu! Wybierz inny termin lub zmień typ na Kartkówkę.</span>";
            }
        }
        // -------------------------------------------------------------

        // Jeśli nie ma błędu limitu, dodajemy wpis
        if (!$blad_limitu) {
            try {
                $sql = "INSERT INTO Sprawdzian (id_przedmiot_w_klasie, data, typ, opis) VALUES (:id, :data, :typ, :opis)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([':id' => $id_pwk, ':data' => $data, ':typ' => $typ, ':opis' => $opis]);
                $komunikat = "Dodano wpis do terminarza!";
            } catch (PDOException $e) {
                $komunikat = "Błąd bazy danych: " . $e->getMessage();
            }
        }
    }
}

// 3. Obsługa USUWANIA sprawdzianu
if (isset($_POST['usun_sprawdzian'])) {
    $id_del = $_POST['id_sprawdzianu'];
    // Zabezpieczenie: sprawdzamy czy ten sprawdzian należy do tego nauczyciela
    $chk = $conn->prepare("SELECT s.id_sprawdzianu FROM Sprawdzian s JOIN Przedmiot_w_klasie pwk ON s.id_przedmiot_w_klasie = pwk.id_przedmiot_w_klasie WHERE s.id_sprawdzianu = :id AND pwk.id_nauczyciela = :nauczyciel");
    $chk->execute([':id' => $id_del, ':nauczyciel' => $id_nauczyciela]);
    
    if ($chk->rowCount() > 0) {
        $del = $conn->prepare("DELETE FROM Sprawdzian WHERE id_sprawdzianu = :id");
        $del->execute([':id' => $id_del]);
        $komunikat = "Usunięto wpis.";
    } else {
        $komunikat = "Błąd: Nie masz uprawnień do usunięcia tego wpisu.";
    }
}


// 4. Pobieranie listy klas i przedmiotów tego nauczyciela (do listy rozwijanej)
$sql_klasy = "SELECT pwk.id_przedmiot_w_klasie, k.nazwa as klasa, p.nazwa as przedmiot
              FROM Przedmiot_w_klasie pwk
              JOIN Klasa k ON pwk.id_klasy = k.id_klasy
              JOIN Przedmiot p ON pwk.id_przedmiotu = p.id_przedmiotu
              WHERE pwk.id_nauczyciela = :nid
              ORDER BY k.nazwa, p.nazwa";
$stmt_k = $conn->prepare($sql_klasy);
$stmt_k->execute([':nid' => $id_nauczyciela]);
$moje_klasy = $stmt_k->fetchAll(PDO::FETCH_ASSOC);

// Sprawdzamy, czy wybrano konkretną klasę (z GET lub POST)
$aktywne_pwk = isset($_GET['id_pwk']) ? $_GET['id_pwk'] : (isset($_POST['id_przedmiot_w_klasie']) ? $_POST['id_przedmiot_w_klasie'] : null);

$lista_sprawdzianow = [];
if ($aktywne_pwk) {
    // Pobierz sprawdziany dla wybranej klasy (nadchodzące i przeszłe)
    $stmt_s = $conn->prepare("SELECT * FROM Sprawdzian WHERE id_przedmiot_w_klasie = :id ORDER BY data DESC");
    $stmt_s->execute([':id' => $aktywne_pwk]);
    $lista_sprawdzianow = $stmt_s->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Terminarz Sprawdzianów</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f6f9; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        
        .top-bar { margin-bottom: 20px; display:flex; justify-content: space-between; align-items: center;}
        .btn-back { text-decoration: none; color: #555; background: white; padding: 8px 15px; border: 1px solid #ccc; border-radius: 5px; }

        .card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
        h1, h2 { color: #333; margin-top: 0; }

        select, input, button { padding: 10px; border: 1px solid #ddd; border-radius: 5px; margin-right: 10px; }
        .btn-select { background: #2196F3; color: white; border: none; cursor: pointer; }
        .btn-add { background: #4CAF50; color: white; border: none; cursor: pointer; font-weight: bold; }
        
        /* Tabela z listą */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #eee; text-align: left; padding: 12px; color: #555; }
        td { border-bottom: 1px solid #eee; padding: 12px; }
        
        /* Oznaczenia typów */
        .badge { padding: 5px 10px; border-radius: 15px; font-size: 12px; font-weight: bold; color: white; }
        .b-sprawdzian { background-color: #f44336; } /* Czerwony */
        .b-kartkowka { background-color: #FF9800; } /* Pomarańczowy */
        .b-inne { background-color: #2196F3; } /* Niebieski */
        
        .date-box { font-weight: bold; color: #333; font-size: 16px; }
        .past-date { opacity: 0.5; } /* Styl dla starych sprawdzianów */

        .msg { background: #e8f5e9; color: green; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>

<div class="container">
    <div class="top-bar">
        <h1>📅 Kalendarz Sprawdzianów</h1>
        <a href="nauczyciel.php" class="btn-back">← Wróć do panelu</a>
    </div>

    <?php if($komunikat): ?>
        <div class="msg"><?php echo $komunikat; ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="GET">
            <label style="font-weight:bold; margin-right: 10px;">Wybierz klasę i przedmiot:</label>
            <select name="id_pwk" required>
                <option value="">-- Wybierz z listy --</option>
                <?php foreach($moje_klasy as $k): ?>
                    <option value="<?php echo $k['id_przedmiot_w_klasie']; ?>" <?php if($aktywne_pwk == $k['id_przedmiot_w_klasie']) echo 'selected'; ?>>
                        Klasa <?php echo $k['klasa']; ?> - <?php echo $k['przedmiot']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-select">Pokaż terminarz</button>
        </form>
    </div>

    <?php if($aktywne_pwk): ?>
        
        <div class="card" style="border-left: 5px solid #4CAF50;">
            <h2>Dodaj nowy termin</h2>
            <form method="POST" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <input type="hidden" name="id_przedmiot_w_klasie" value="<?php echo $aktywne_pwk; ?>">
                
                <div>
                    <label style="display:block; font-size:12px;">Data:</label>
                    <input type="date" name="data" required>
                </div>
                
                <div>
                    <label style="display:block; font-size:12px;">Rodzaj:</label>
                    <select name="typ">
                        <option value="Sprawdzian">Sprawdzian</option>
                        <option value="Kartkówka">Kartkówka</option>
                        <option value="Zadanie">Zadanie / Projekt</option>
                    </select>
                </div>

                <div style="flex-grow: 1;">
                    <label style="display:block; font-size:12px;">Opis (zakres materiału):</label>
                    <input type="text" name="opis" placeholder="np. Ułamki zwykłe i dziesiętne" style="width: 95%;" required>
                </div>

                <div style="align-self: flex-end;">
                    <button type="submit" name="dodaj_sprawdzian" class="btn-add">+ Dodaj</button>
                </div>
            </form>
        </div>

        <div class="card">
            <h2>Zaplanowane wydarzenia</h2>
            <?php if(count($lista_sprawdzianow) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Typ</th>
                            <th>Zakres materiału</th>
                            <th>Akcja</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($lista_sprawdzianow as $s): ?>
                            <?php 
                                // Czy data jest z przeszłości?
                                $is_past = (strtotime($s['data']) < strtotime(date('Y-m-d')));
                                $klasa_stylu = $is_past ? 'past-date' : '';
                                
                                // Kolor badge'a
                                $badge_class = 'b-inne';
                                if($s['typ'] == 'Sprawdzian') $badge_class = 'b-sprawdzian';
                                if($s['typ'] == 'Kartkówka') $badge_class = 'b-kartkowka';
                            ?>
                            <tr class="<?php echo $klasa_stylu; ?>">
                                <td>
                                    <span class="date-box"><?php echo $s['data']; ?></span>
                                    <?php if($is_past) echo "<br><small>(Archiwum)</small>"; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo $s['typ']; ?></span>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($s['opis']); ?></strong>
                                </td>
                                <td>
                                    <form method="POST" onsubmit="return confirm('Czy na pewno usunąć ten wpis?');">
                                        <input type="hidden" name="id_sprawdzianu" value="<?php echo $s['id_sprawdzianu']; ?>">
                                        <input type="hidden" name="id_przedmiot_w_klasie" value="<?php echo $aktywne_pwk; ?>"> <button type="submit" name="usun_sprawdzian" style="background:none; border:none; color:red; cursor:pointer; font-size:20px;" title="Usuń">🗑️</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align:center; color:#777; padding: 20px;">Brak zaplanowanych sprawdzianów dla tej klasy.</p>
            <?php endif; ?>
        </div>

    <?php endif; ?>

</div>

</body>
</html>