<?php
session_start();
require_once '../db.php';

// Zabezpieczenie
if (!isset($_SESSION['rola']) || $_SESSION['rola'] !== 'nauczyciel') {
    header("Location: ../index.php");
    exit;
}
if (!isset($_SESSION['aktywne_zajecia_id'])) {
    header("Location: lekcje.php");
    exit;
}

$id_zajec = $_SESSION['aktywne_zajecia_id'];
$komunikat = "";

// Pobieramy opis tematu z linku 
$domyslny_opis = isset($_GET['opis_tematu']) ? urldecode($_GET['opis_tematu']) : "";

// . OBSŁUGA DODAWANIA OCENY 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['dodaj_ocene'])) {
    
    $waga = $_POST['waga'];
    $kategoria = $_POST['kategoria']; // np. "Kartkówka"
    $opis = isset($_POST['opis']) ? trim($_POST['opis']) : ''; // np. "Ułamki"
    
    if (!empty($opis)) {
        $typ_do_bazy = $kategoria . " (" . $opis . ")";
    } else {
        $typ_do_bazy = $kategoria;
    }

    $data = date('Y-m-d');
    $licznik_dodanych = 0;

    if (isset($_POST['ocena']) && is_array($_POST['ocena'])) {
        try {
            $conn->beginTransaction();
            
           
            $sql = "INSERT INTO Ocena (wartosc, waga, data_wystawienia, typ, id_ucznia, id_przedmiot_w_klasie) 
                    VALUES (:wartosc, :waga, :data, :typ, :id_ucznia, :id_zajec)";
            $stmt = $conn->prepare($sql);

            foreach ($_POST['ocena'] as $id_ucznia => $wartosc) {
                $wartosc = trim($wartosc);
                
                if ($wartosc !== '' && $wartosc !== null) {
                    $stmt->execute([
                        ':wartosc' => $wartosc,
                        ':waga' => $waga,
                        ':data' => $data,
                        ':typ' => $typ_do_bazy, 
                        ':id_ucznia' => $id_ucznia,
                        ':id_zajec' => $id_zajec
                    ]);
                    $licznik_dodanych++;
                }
            }
            
            $conn->commit();
            
            if ($licznik_dodanych > 0) {
                $komunikat = "Pomyślnie dodano $licznik_dodanych ocen.";
                $domyslny_opis = ""; 
            } else {
                $komunikat = "Nie wpisano żadnych ocen.";
            }

        } catch (PDOException $e) {
            $conn->rollBack();
            $komunikat = "Błąd bazy danych: " . $e->getMessage();
        }
    }
}

//POBIERANIE DANYCH
$stmt_info = $conn->prepare("SELECT k.nazwa as klasa, p.nazwa as przedmiot FROM Przedmiot_w_klasie pwk JOIN Klasa k ON pwk.id_klasy = k.id_klasy JOIN Przedmiot p ON pwk.id_przedmiotu = p.id_przedmiotu WHERE pwk.id_przedmiot_w_klasie = :id");
$stmt_info->execute([':id' => $id_zajec]);
$lekcja_info = $stmt_info->fetch(PDO::FETCH_ASSOC);

$stmt_klasa_id = $conn->prepare("SELECT id_klasy FROM Przedmiot_w_klasie WHERE id_przedmiot_w_klasie = :id");
$stmt_klasa_id->execute([':id' => $id_zajec]);
$id_klasy = $stmt_klasa_id->fetchColumn();

$stmt_uczniowie = $conn->prepare("SELECT u.imie, u.nazwisko, ucz.nr_dziennika, ucz.id_ucznia FROM Uczen ucz JOIN Uzytkownik u ON ucz.id_uzytkownika = u.id_uzytkownika WHERE ucz.id_klasy = :id_klasy ORDER BY ucz.nr_dziennika ASC");
$stmt_uczniowie->execute([':id_klasy' => $id_klasy]);
$uczniowie = $stmt_uczniowie->fetchAll(PDO::FETCH_ASSOC);


$stmt_oceny = $conn->prepare("SELECT id_ucznia, wartosc, waga, typ, data_wystawienia FROM Ocena WHERE id_przedmiot_w_klasie = :id_zajec");
$stmt_oceny->execute([':id_zajec' => $id_zajec]);
$wszystkie_oceny = $stmt_oceny->fetchAll(PDO::FETCH_ASSOC);

$oceny_uczniow = [];
foreach ($wszystkie_oceny as $o) {
    $oceny_uczniow[$o['id_ucznia']][] = $o;
}

function obliczSrednia($oceny) {
    if (empty($oceny)) return "-";
    $suma = 0; $suma_wag = 0;
    foreach ($oceny as $o) {
        $wartosc = $o['wartosc'];
        if (strpos($wartosc, '+') !== false) $val = floatval($wartosc) + 0.5;
        elseif (strpos($wartosc, '-') !== false) $val = floatval($wartosc) - 0.25;
        else $val = floatval($wartosc);
        if ($val > 0) { $suma += $val * $o['waga']; $suma_wag += $o['waga']; }
    }
    return ($suma_wag == 0) ? "-" : round($suma / $suma_wag, 2);
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Wystawianie ocen</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px; color: #333; }
        .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; background: white; padding: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-radius: 5px; }
        .btn-powrot { padding: 8px 20px; border: 1px solid #ccc; background: #fff; text-decoration: none; color: #333; border-radius: 5px; }
        .info-box { text-align: center; font-size: 20px; margin-bottom: 20px; }
        
        .tools-bar { 
            background-color: #fff; padding: 20px; border-left: 5px solid #00bcd4; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-radius: 5px; margin-bottom: 20px; 
            display: flex; gap: 15px; align-items: center; justify-content: center; flex-wrap: wrap;
        }
        
        select, input[type="text"] { padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        input[name="opis"] { width: 300px; border: 2px solid #00bcd4; font-weight: bold; }

        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
        th { background-color: #00bcd4; color: white; padding: 12px; border: 1px solid #ddd; }
        td { border: 1px solid #ddd; padding: 10px; text-align: center; vertical-align: middle; }
        td.align-left { text-align: left; }

        .grade-badge { 
            display: inline-block; 
            padding: 5px 10px; 
            margin: 2px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            background: #fff; 
            font-weight: bold;
            font-size: 15px;
            color: #333;
            cursor: help; 
            position: relative;
        }
        .grade-badge:hover {
            background-color: #e0f7fa;
            border-color: #00bcd4;
        }

        .grade-input { width: 50px; text-align: center; padding: 5px; border: 1px solid #ccc; border-radius: 4px; font-size: 16px; }
        .btn-zapisz { background-color: #4CAF50; color: white; padding: 10px 25px; border: none; cursor: pointer; font-size: 16px; border-radius: 4px; font-weight: bold; }
        .komunikat { text-align: center; color: #2e7d32; font-weight: bold; margin-bottom: 15px; padding: 10px; background: #e8f5e9; border-radius: 5px; }
    </style>
</head>
<body>

    <div class="header-bar">
        <a href="javascript:history.back()" class="btn-powrot">← Powrót</a>
        <div>Zalogowany: <strong><?php echo $_SESSION['imie'] . ' ' . $_SESSION['nazwisko']; ?></strong></div>
    </div>

    <div class="info-box">
        Klasa: <strong style="color: #00bcd4;"><?php echo $lekcja_info['klasa']; ?></strong> | 
        Przedmiot: <strong style="color: #00bcd4;"><?php echo $lekcja_info['przedmiot']; ?></strong>
    </div>

    <?php if ($komunikat): ?>
        <div class="komunikat"><?php echo $komunikat; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="tools-bar">
            <span><strong>Ustawienia oceny:</strong></span>
            
            <label>Za co:</label>
            <input type="text" name="opis" value="<?php echo htmlspecialchars($domyslny_opis); ?>" placeholder="np. Ułamki (opcjonalne)">

            <label>Kategoria:</label>
            <select name="kategoria">
                <option value="Kartkówka">Kartkówka</option>
                <option value="Sprawdzian">Sprawdzian</option>
                <option value="Odpowiedź ustna">Odpowiedź ustna</option>
                <option value="Praca domowa">Praca domowa</option>
                <option value="Aktywność">Aktywność</option>
            </select>
            
            <label>Waga:</label>
            <select name="waga">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3" selected>3</option>
                <option value="4">4</option>
                <option value="5">5</option>
            </select>

            <button type="submit" name="dodaj_ocene" class="btn-zapisz">Zapisz</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">Nr</th>
                    <th>Uczeń</th>
                    <th>Oceny (najedź myszką by zobaczyć opis)</th>
                    <th style="width: 100px; background-color: #e0f7fa; color: #006064;">Nowa ocena</th>
                    <th style="width: 80px;">Średnia</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($uczniowie as $uczen): ?>
                <?php 
                    $id = $uczen['id_ucznia'];
                    $oceny_tego_ucznia = isset($oceny_uczniow[$id]) ? $oceny_uczniow[$id] : [];
                    $srednia = obliczSrednia($oceny_tego_ucznia);
                ?>
                <tr>
                    <td><?php echo $uczen['nr_dziennika']; ?></td>
                    <td class="align-left">
                        <strong><?php echo $uczen['nazwisko']; ?></strong> <?php echo $uczen['imie']; ?>
                    </td>
                    <td class="align-left">
                        <?php foreach ($oceny_tego_ucznia as $o): ?>
                            <span class="grade-badge" title="<?php echo $o['typ']; ?>&#013;Data: <?php echo $o['data_wystawienia']; ?>&#013;Waga: <?php echo $o['waga']; ?>">
                                <?php echo $o['wartosc']; ?>
                            </span>
                        <?php endforeach; ?>
                    </td>
                    <td style="background-color: #e0f7fa;">
                        <input type="text" class="grade-input" name="ocena[<?php echo $id; ?>]" placeholder="-" maxlength="4">
                    </td>
                    <td style="font-weight: bold; <?php if($srednia < 2 && $srednia != "-") echo 'color:#d32f2f;'; ?>">
                        <?php echo $srednia; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </form>

</body>
</html>