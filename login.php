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
$userLogin = $_POST['login'];
$userPassword = $_POST['password'];

// Use prepared statement to prevent SQL injection
$stmt = mysqli_prepare($connection, "SELECT haslo FROM uzytkownik WHERE login = ?");
mysqli_stmt_bind_param($stmt, "s", $userLogin);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $storedHashedPassword = $row['haslo'];

    // Verify the hashed password against the user's input
    if (password_verify($userPassword, $storedHashedPassword)) {
        echo "Login successful!";
    } else {
        echo "Invalid credentials!";
    }
} else {
    echo "User not found!";
}

mysqli_stmt_close($stmt);

mysqli_close($connection);
?>