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

// SQL query to fetch the stored hashed password
$query = "SELECT haslo FROM uzytkownik WHERE login = '$userLogin'";
$result = mysqli_query($connection, $query);

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

mysqli_close($connection);
?>