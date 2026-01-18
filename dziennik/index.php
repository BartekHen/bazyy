<?php
session_start();
require_once 'db.php';

$error = "";

// LOGIKA LOGOWANIA
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_email = trim($_POST['email']);
    $haslo = trim($_POST['haslo']);

    if (!empty($login_email) && !empty($haslo)) {
        // Sprawdzamy użytkownika w bazie
        $stmt = $conn->prepare("SELECT * FROM Uzytkownik WHERE email = :email OR login = :login");
        $stmt->bindParam(':email', $login_email);
        $stmt->bindParam(':login', $login_email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $haslo === $user['haslo']) {
            $_SESSION['user_id'] = $user['id_uzytkownika'];
            $_SESSION['rola'] = $user['rola'];
            $_SESSION['imie'] = $user['imie'];
            $_SESSION['nazwisko'] = $user['nazwisko'];

            // Przekierowanie w zależności od roli
            if ($user['rola'] == 'admin') {
                header("Location: admin/admin.php");
            } elseif ($user['rola'] == 'nauczyciel') {
                header("Location: nauczyciel/nauczyciel.php");
            } elseif ($user['rola'] == 'uczen') {
                header("Location: uczen/uczen.php");
            } elseif ($user['rola'] == 'sekretariat') {
                 header("Location: sekretariat/sekretariat.php");
            }
             elseif ($user['rola'] == 'rodzic') {
            
                header("Location: rodzic/rodzic.php");
            }
            exit;
        } else {
            $error = "Błędny login lub hasło.";
        }
    } else {
        $error = "Wypełnij wszystkie pola.";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>E-Dziennik - Logowanie</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        /* Główna karta */
        .login-card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
        }

        p.subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        /* Style formularza */
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            display: inline-block;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; 
            font-size: 16px;
        }

        button {
            width: 100%;
            background-color: #4CAF50;
            color: white;
            padding: 14px 20px;
            margin: 20px 0 10px 0;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: 0.3s;
        }
        button:hover {
            background-color: #45a049;
        }

        .error-msg {
            color: red;
            font-size: 14px;
            margin-bottom: 15px;
        }

        /* SEKCJA GOŚCIA */
        .guest-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .guest-label {
            font-size: 14px;
            color: #888;
            margin-bottom: 10px;
            display: block;
        }

        .btn-guest {
            display: inline-block;
            text-decoration: none;
            color: #2196F3;
            border: 2px solid #2196F3;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: bold;
            font-size: 14px;
            transition: 0.3s;
            width: 80%; 
        }
        .btn-guest:hover {
            background-color: #e3f2fd;
        }

    </style>
</head>
<body>

    <div class="login-card">
        <h1>E-Dziennik</h1>
        <p class="subtitle">Zaloguj się do systemu</p>

        <?php if($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="email" placeholder="E-mail" required>
            <input type="password" name="haslo" placeholder="Hasło" required>
            
            <button type="submit">Zaloguj się</button>
        </form>

        <div class="guest-section">
            <span class="guest-label">Nie masz konta?</span>
            <a href="informacje.php" class="btn-guest">
                Informacje o szkole
            </a>
        </div>

    </div>

</body>
</html>