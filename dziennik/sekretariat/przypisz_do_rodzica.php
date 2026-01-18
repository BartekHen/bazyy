<?php
session_start();
require_once '../db.php';


if (!isset($_SESSION['rola']) || $_SESSION['rola'] !== 'sekretariat') {
    header("Location: ../index.php");
    exit;
}

$komunikat = "";

// DODAWANIE POWIĄZANIA 

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['przypisz'])) {
    $id_rodzica = $_POST['id_rodzica'];
    $id_ucznia = $_POST['id_ucznia'];

    if ($id_rodzica && $id_ucznia) {
        
        $chk = $conn->prepare("SELECT id_ucznia FROM Opieka WHERE id_rodzica = :r AND id_ucznia = :u");
        $chk->execute([':r' => $id_rodzica, ':u' => $id_ucznia]);

        if ($chk->rowCount() == 0) {
            try {
                $stmt = $conn->prepare("INSERT INTO Opieka (id_rodzica, id_ucznia) VALUES (:r, :u)");
                $stmt->execute([':r' => $id_rodzica, ':u' => $id_ucznia]);
                $komunikat = "Sukces! Przypisano ucznia do rodzica.";
            } catch (PDOException $e) {
                $komunikat = "Błąd bazy: " . $e->getMessage();
            }
        } else {
            $komunikat = "To przypisanie już istnieje!";
        }
    }
}

// USUWANIE POWIĄZANIA

if (isset($_POST['usun_powiazanie'])) {
    $id_r_usun = $_POST['id_rodzica_usun'];
    $id_u_usun = $_POST['id_ucznia_usun'];

    $stmt = $conn->prepare("DELETE FROM Opieka WHERE id_rodzica = :r AND id_ucznia = :u");
    $stmt->execute([':r' => $id_r_usun, ':u' => $id_u_usun]);
    $komunikat = "Usunięto powiązanie.";
}

// POBIERANIE DANYCH
$sql_rodzice = "SELECT r.id_rodzica, u.imie, u.nazwisko, u.email 
                FROM Rodzic r JOIN Uzytkownik u ON r.id_uzytkownika = u.id_uzytkownika 
                ORDER BY u.nazwisko";
$lista_rodzicow = $conn->query($sql_rodzice)->fetchAll(PDO::FETCH_ASSOC);

$sql_uczniowie = "SELECT ucz.id_ucznia, u.imie, u.nazwisko, k.nazwa as klasa
                  FROM Uczen ucz 
                  JOIN Uzytkownik u ON ucz.id_uzytkownika = u.id_uzytkownika
                  LEFT JOIN Klasa k ON ucz.id_klasy = k.id_klasy
                  ORDER BY k.nazwa, u.nazwisko";
$lista_uczniow = $conn->query($sql_uczniowie)->fetchAll(PDO::FETCH_ASSOC);

$sql_opieka = "SELECT o.id_rodzica, o.id_ucznia,
                      ur.imie as r_imie, ur.nazwisko as r_nazwisko,
                      uu.imie as u_imie, uu.nazwisko as u_nazwisko,
                      k.nazwa as klasa
               FROM Opieka o
               JOIN Rodzic r ON o.id_rodzica = r.id_rodzica
               JOIN Uzytkownik ur ON r.id_uzytkownika = ur.id_uzytkownika
               JOIN Uczen ucz ON o.id_ucznia = ucz.id_ucznia
               JOIN Uzytkownik uu ON ucz.id_uzytkownika = uu.id_uzytkownika
               LEFT JOIN Klasa k ON ucz.id_klasy = k.id_klasy
               ORDER BY ur.nazwisko DESC";
$istniejace = $conn->query($sql_opieka)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Przypisz Rodzica (Sekretariat)</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f6f9; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        
        .top-bar { margin-bottom: 20px; }
        .btn-powrot { 
            padding: 8px 20px; border: 1px solid #ccc; background: white; 
            text-decoration: none; color: #333; border-radius: 5px; font-weight: bold;
        }

        .card { 
            background: white; padding: 30px; margin-bottom: 30px; 
            border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
        }

        h2 { margin-top: 0; color: #333; border-bottom: 2px solid #eee; padding-bottom: 15px; }

        /* Formularz */
        .form-row { display: flex; gap: 20px; align-items: flex-end; }
        .form-group { flex: 1; }
        
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; background: #fff; }
        
        .btn-submit { 
            padding: 10px 30px; background: #4CAF50; color: white; border: none; 
            border-radius: 5px; cursor: pointer; font-weight: bold; height: 42px;
        }
        .btn-submit:hover { background: #45a049; }

        /* Tabela */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #eee; padding: 12px; text-align: left; color: #555; }
        td { border-bottom: 1px solid #eee; padding: 12px; color: #333; }
        
        .btn-del { 
            color: white; background: #e57373; padding: 5px 10px; 
            text-decoration: none; border-radius: 4px; font-size: 12px; border: none; cursor: pointer;
        }
        .btn-del:hover { background: #d32f2f; }

        .msg { padding: 15px; background: #e8f5e9; color: #2e7d32; border-radius: 5px; margin-bottom: 20px; font-weight: bold; text-align: center; }
    </style>
</head>
<body>

<div class="container">
    <div class="top-bar">
        <a href="sekretariat.php" class="btn-powrot">← Powrót do panelu</a>
    </div>

    <?php if($komunikat): ?>
        <div class="msg"><?php echo $komunikat; ?></div>
    <?php endif; ?>

    <div class="card">
        <h2>Nowe przypisanie</h2>
        <form method="POST">
            <div class="form-row">
                
                <div class="form-group">
                    <label>Rodzic:</label>
                    <select name="id_rodzica" required>
                        <option value="">-- Wybierz Rodzica --</option>
                        <?php foreach ($lista_rodzicow as $r): ?>
                            <option value="<?php echo $r['id_rodzica']; ?>">
                                <?php echo $r['nazwisko'] . " " . $r['imie'] . " (" . $r['email'] . ")"; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Uczeń:</label>
                    <select name="id_ucznia" required>
                        <option value="">-- Wybierz Ucznia --</option>
                        <?php foreach ($lista_uczniow as $u): ?>
                            <option value="<?php echo $u['id_ucznia']; ?>">
                                <?php echo $u['nazwisko'] . " " . $u['imie'] . " (Kl. " . ($u['klasa'] ?? 'Brak') . ")"; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" name="przypisz" class="btn-submit">Połącz ich</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Aktualne powiązania</h2>
        <?php if(count($istniejace) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Rodzic</th>
                        <th>Uczeń</th>
                        <th>Klasa ucznia</th>
                        <th>Akcja</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($istniejace as $row): ?>
                    <tr>
                        <td style="font-weight:bold;">
                            <?php echo $row['r_nazwisko'] . " " . $row['r_imie']; ?>
                        </td>
                        <td>
                            <?php echo $row['u_nazwisko'] . " " . $row['u_imie']; ?>
                        </td>
                        <td>
                            <?php echo $row['klasa'] ?? '-'; ?>
                        </td>
                        <td>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Na pewno usunąć to powiązanie?');">
                                <input type="hidden" name="id_rodzica_usun" value="<?php echo $row['id_rodzica']; ?>">
                                <input type="hidden" name="id_ucznia_usun" value="<?php echo $row['id_ucznia']; ?>">
                                <button type="submit" name="usun_powiazanie" class="btn-del">Usuń</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color:#777; text-align:center;">Brak przypisanych rodziców.</p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>