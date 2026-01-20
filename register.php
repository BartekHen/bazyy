<?php
// Database connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "dziennik_szkolny";

$connection = mysqli_connect($host, $user, $password, $dbname);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Example user input
$userLogin = $_POST['login'] ?? '';
$userPassword = $_POST['password'] ?? '';
$userName = $_POST['name'] ?? '';
$userSurname = $_POST['surname'] ?? '';
$userEmail = $_POST['email'] ?? '';

// Input validation
if (empty($userLogin) || empty($userPassword) || empty($userName) || empty($userSurname) || empty($userEmail)) {
    die("Error: All fields are required!");
}

if (strlen($userPassword) < 8) {
    die("Error: Password must be at least 8 characters long!");
}

if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
    die("Error: Invalid email format!");
}

// Hash the user's password before storing it
$hashedPassword = password_hash($userPassword, PASSWORD_BCRYPT);

// Use prepared statement to prevent SQL injection
$stmt = mysqli_prepare($connection, "INSERT INTO uzytkownik (login, haslo, imie, nazwisko, email) VALUES (?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "sssss", $userLogin, $hashedPassword, $userName, $userSurname, $userEmail);

if (mysqli_stmt_execute($stmt)) {
    echo "Registration successful!";
} else {
    echo "Error: " . mysqli_stmt_error($stmt);
}

mysqli_stmt_close($stmt);

mysqli_close($connection);
?>
