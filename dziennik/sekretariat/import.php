<?php
session_start();
require_once '../db.php';

ini_set('auto_detect_line_endings', TRUE);

if (!isset($_SESSION['rola']) || $_SESSION['rola'] !== 'sekretariat') {
    header("Location: ../index.php");
    exit;
}

$komunikat = "";
$bledy = []; // Tablica na błędy

function wykryjRole($tekst) {
    $tekst = mb_strtolower($tekst, 'UTF-8'); 
    if (strpos($tekst, 'ucz') !== false) return 'uczen';
    if (strpos($tekst, 'rodz') !== false) return 'rodzic';
    return false;
}

// Wczytanie pliku
if (isset($_POST['wczytaj'])) {
    if (isset($_FILES['plik_csv']) && $_FILES['plik_csv']['error'] == 0) {
        $plik = $_FILES['plik_csv']['tmp_name'];
        $dane_z_pliku = [];
        
        if (($handle = fopen($plik, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (count($data) == 1 && strpos($data[0], ';') !== false) $data = explode(';', $data[0]);

                if(count($data) >= 3) {
                    $imie = trim($data[0]);
                    $nazwisko = trim($data[1]);
                    $rola_surowa = trim($data[2]);
                    
                    if (!mb_check_encoding($rola_surowa, 'UTF-8')) {
                        $rola_surowa = mb_convert_encoding($rola_surowa, 'UTF-8', 'Windows-1250');
                        $imie = mb_convert_encoding($imie, 'UTF-8', 'Windows-1250');
                        $nazwisko = mb_convert_encoding($nazwisko, 'UTF-8', 'Windows-1250');
                    }

                    $rola_baza = wykryjRole($rola_surowa);

                    if (strtolower($imie) == 'imie' || strtolower($imie) == 'imię') continue;

                    if($rola_baza) {
                        $dane_z_pliku[] = [
                            'imie' => $imie,
                            'nazwisko' => $nazwisko,
                            'rola' => $rola_baza,
                            'rola_org' => $rola_surowa
                        ];
                    }
                }
            }
            fclose($handle);
            $_SESSION['import_data'] = $dane_z_pliku;
        }
    } else {
        $komunikat = "Wybierz poprawny plik CSV.";
    }
}

// Import do bazy 
if (isset($_POST['potwierdz_import']) && isset($_SESSION['import_data'])) {
    $licznik_uzytkownikow = 0;
    $licznik_uczniow = 0;
    $licznik_rodzicow = 0;
    
    foreach ($_SESSION['import_data'] as $index => $osoba) {
        try {
            $conn->beginTransaction();

            $imie = $osoba['imie'];
            $nazwisko = $osoba['nazwisko'];
            $rola = $osoba['rola'];

            // Login
            $imie_ascii = iconv('UTF-8', 'ASCII//TRANSLIT', $imie);
            $nazwisko_ascii = iconv('UTF-8', 'ASCII//TRANSLIT', $nazwisko);
            $login_base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $imie_ascii . "." . $nazwisko_ascii));
            if (empty($login_base)) $login_base = "user" . rand(1000,9999);
            $email = $login_base . rand(100, 999) . "@szkola.pl";
            $haslo = "start123";

            // 1. INSERT Uzytkownik
            $stmt = $conn->prepare("INSERT INTO Uzytkownik (login, haslo, imie, nazwisko, email, rola) VALUES (:email, :haslo, :imie, :nazwisko, :email, :rola)");
            $stmt->execute([':email'=>$email, ':haslo'=>$haslo, ':imie'=>$imie, ':nazwisko'=>$nazwisko, ':rola'=>$rola]);
            $id_user = $conn->lastInsertId();

            // 2. INSERT Uczen / Rodzic
            if ($rola == 'uczen') {
                $stmt_u = $conn->prepare("INSERT INTO Uczen (id_uzytkownika, id_klasy, nr_dziennika) VALUES (:uid, NULL, 0)");
                $stmt_u->execute([':uid' => $id_user]);
                $licznik_uczniow++; 
            } 
            elseif ($rola == 'rodzic') {
                $stmt_r = $conn->prepare("INSERT INTO Rodzic (id_uzytkownika) VALUES (:uid)");
                $stmt_r->execute([':uid' => $id_user]);
                $licznik_rodzicow++; 
            }

            $conn->commit();
            $licznik_uzytkownikow++;

        } catch (PDOException $e) {
            $conn->rollBack();
            $bledy[] = "Błąd przy osobie <strong>$imie $nazwisko</strong>: " . $e->getMessage();
        }
    }

    $komunikat = "<span style='color:green'>Sukces! Dodano: $licznik_uzytkownikow kont (U: $licznik_uczniow, R: $licznik_rodzicow).</span>";
    
    if (count($bledy) > 0) {
        $komunikat .= "<br><br><strong style='color:red'>Wystąpiły błędy:</strong><br>" . implode("<br>", $bledy);
    } else {
        unset($_SESSION['import_data']); 
    }
}

$podglad = isset($_SESSION['import_data']) ? $_SESSION['import_data'] : [];
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Import Kont (Sekretariat)</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f9f9f9; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        
        /* Styl przycisku powrotu */
        .btn-back { 
            position: absolute; top: 20px; left: 20px; 
            padding: 8px 20px; border: 1px solid #ccc; background: #fff; 
            text-decoration: none; color: #333; border-radius: 5px; font-size: 14px; 
        }
        
        h1 { font-weight: normal; margin-bottom: 40px; color: #333; }
        .file-upload-row { display: flex; justify-content: center; align-items: center; gap: 20px; margin-bottom: 40px; }
        input[type="file"] { background: #e0e0e0; padding: 5px; border-radius: 4px; width: 300px; color: #333; }
        .btn-load { background: #e0e0e0; border: 1px solid #999; padding: 6px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; border: 2px solid #666; }
        th { background: #ccc; border: 1px solid #666; padding: 10px; font-weight: bold; }
        td { border: 1px solid #666; padding: 10px; font-size: 18px; }
        .btn-import { background: #e0e0e0; border: 2px solid #666; padding: 10px 40px; border-radius: 5px; font-size: 18px; font-weight: bold; cursor: pointer; width: 100%; max-width: 400px; }
        .btn-import:hover { background: #d0d0d0; }
        .msg { font-size: 16px; margin-bottom: 20px; padding: 10px; border: 1px solid #ddd; background: #fff; }
        .info { background: #e3f2fd; padding: 10px; border-radius: 5px; color: #0d47a1; margin-bottom: 20px; font-size: 14px; }
    </style>
</head>
<body>
    <a href="sekretariat.php" class="btn-back">Powrót do panelu</a>
    
    <div class="container">
        <h1>Import kont użytkowników</h1>
        <div class="info">Format CSV: <strong>Imię, Nazwisko, Rola</strong></div>

        <?php if($komunikat): ?>
            <div class="msg"><?php echo $komunikat; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="file-upload-row">
                <span style="font-size: 18px;">Wybierz plik:</span>
                <input type="file" name="plik_csv" accept=".csv" required>
                <button type="submit" name="wczytaj" class="btn-load">Wczytaj</button>
            </div>
        </form>

        <?php if (!empty($podglad)): ?>
            <div style="text-align: left; margin-bottom: 10px; font-size: 18px;">Podgląd pliku:</div>
            <table>
                <thead>
                    <tr><th>Imię</th><th>Nazwisko</th><th>Rola</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($podglad as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['imie']); ?></td>
                        <td><?php echo htmlspecialchars($row['nazwisko']); ?></td>
                        <td style="color:green;font-weight:bold;"><?php echo ucfirst($row['rola']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <form method="POST">
                <button type="submit" name="potwierdz_import" class="btn-import">Importuj użytkowników</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>