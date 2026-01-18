<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['rola']) || $_SESSION['rola'] !== 'nauczyciel') {
    header("Location: ../index.php");
    exit;
}

// Pobieramy ID lekcji
$id_lekcji = isset($_GET['id_lekcji']) ? $_GET['id_lekcji'] : null;

if (!$id_lekcji) {
    die("Błąd: Nie wybrano lekcji. Wróć do listy tematów.");
}

$komunikat = "";

//ZAPISYWANIE OBECNOŚCI 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['zapisz_obecnosc'])) {
    try {
        $conn->beginTransaction();

        $stmt_del = $conn->prepare("DELETE FROM Nieobecnosc WHERE id_lekcji = :id_lekcji");
        $stmt_del->execute([':id_lekcji' => $id_lekcji]);

        if (isset($_POST['nieobecny']) && is_array($_POST['nieobecny'])) {
            $stmt_ins = $conn->prepare("INSERT INTO Nieobecnosc (id_ucznia, id_lekcji, powod) VALUES (:id_ucznia, :id_lekcji, 'Nieobecny')");
            
            foreach ($_POST['nieobecny'] as $id_ucznia) {
                $stmt_ins->execute([
                    ':id_ucznia' => $id_ucznia,
                    ':id_lekcji' => $id_lekcji
                ]);
            }
        }

        $conn->commit();
        $komunikat = "Zapisano frekwencję.";
    } catch (PDOException $e) {
        $conn->rollBack();
        $komunikat = "Błąd: " . $e->getMessage();
    }
}

// POBRANIE DANYCH O LEKCJI 
$stmt_l = $conn->prepare("SELECT temat, data, id_przedmiot_w_klasie FROM Lekcja WHERE id_lekcji = :id");
$stmt_l->execute([':id' => $id_lekcji]);
$lekcja = $stmt_l->fetch(PDO::FETCH_ASSOC);

// POBRANIE UCZNIÓW I ICH STATUSU 
// Najpierw pobieramy klasę
$stmt_k = $conn->prepare("SELECT id_klasy FROM Przedmiot_w_klasie WHERE id_przedmiot_w_klasie = :id");
$stmt_k->execute([':id' => $lekcja['id_przedmiot_w_klasie']]);
$id_klasy = $stmt_k->fetchColumn();

// Pobieramy listę uczniów + sprawdzamy LEFT JOIN-em czy są w tabeli Nieobecnosc
$sql = "SELECT u.imie, u.nazwisko, ucz.nr_dziennika, ucz.id_ucznia, n.id_lekcji as czy_nieobecny
        FROM Uczen ucz
        JOIN Uzytkownik u ON ucz.id_uzytkownika = u.id_uzytkownika
        LEFT JOIN Nieobecnosc n ON ucz.id_ucznia = n.id_ucznia AND n.id_lekcji = :id_lekcji
        WHERE ucz.id_klasy = :id_klasy
        ORDER BY ucz.nr_dziennika ASC";

$stmt_u = $conn->prepare($sql);
$stmt_u->execute([
    ':id_lekcji' => $id_lekcji,
    ':id_klasy' => $id_klasy
]);
$uczniowie = $stmt_u->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Sprawdzanie obecności</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; color: #333; }
        
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        
        .header-info { text-align: center; margin-bottom: 30px; }
        h2 { margin: 0 0 10px 0; color: #333; }
        
        .btn-powrot { text-decoration: none; color: #555; font-weight: bold; display: inline-block; margin-bottom: 20px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background-color: #eee; padding: 12px; text-align: left; border-bottom: 2px solid #ccc; }
        td { padding: 12px; border-bottom: 1px solid #eee; vertical-align: middle; }
        
        
        input[type="checkbox"] { transform: scale(1.5); cursor: pointer; }
        
       
        .row-absent { background-color: #ffebee; color: #c62828; }
        
        .btn-save { 
            display: block; width: 100%; padding: 15px; 
            background-color: #FF9800; color: white; 
            font-size: 18px; border: none; border-radius: 5px; 
            cursor: pointer; font-weight: bold; 
        }
        .btn-save:hover { background-color: #F57C00; }
        
        .msg { text-align: center; color: green; font-weight: bold; margin-bottom: 20px; background: #e8f5e9; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>

    <div class="container">
        <a href="temat.php" class="btn-powrot">← Wróć do listy tematów</a>
        
        <?php if ($komunikat): ?>
            <div class="msg"><?php echo $komunikat; ?></div>
        <?php endif; ?>

        <div class="header-info">
            <h2>Sprawdzanie obecności</h2>
            <p>
                Temat: <strong><?php echo $lekcja['temat']; ?></strong> <br>
                Data: <?php echo $lekcja['data']; ?>
            </p>
            <p style="font-size: 14px; color: #666;">Zaznacz osoby, które są <strong>NIEOBECNE</strong>.</p>
        </div>

        <form method="POST">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">Nr</th>
                        <th>Imię i Nazwisko</th>
                        <th style="width: 100px; text-align: center;">Nieobecny?</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($uczniowie as $u): ?>
                    <tr class="<?php echo $u['czy_nieobecny'] ? 'row-absent' : ''; ?>">
                        <td><?php echo $u['nr_dziennika']; ?></td>
                        <td>
                            <strong><?php echo $u['nazwisko']; ?></strong> <?php echo $u['imie']; ?>
                        </td>
                        <td style="text-align: center;">
                            <input type="checkbox" name="nieobecny[]" value="<?php echo $u['id_ucznia']; ?>" 
                                <?php if ($u['czy_nieobecny']) echo 'checked'; ?>>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <button type="submit" name="zapisz_obecnosc" class="btn-save">Zapisz frekwencję</button>
        </form>
    </div>

</body>
</html>