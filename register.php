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
$userName = $_POST['name'];
$userSurname = $_POST['surname'];
$userEmail = $_POST['email'];

// Hash the user's password before storing it
$hashedPassword = password_hash($userPassword, PASSWORD_BCRYPT);

// SQL query to insert the new user
$query = "INSERT INTO uzytkownik (login, haslo, imie, nazwisko, email) 
          VALUES ('$userLogin', '$hashedPassword', '$userName', '$userSurname', '$userEmail)";

if (mysqli_query($connection, $query)) {
    echo "Registration successful!";
} else {
    echo "Error: " . mysqli_error($connection);
}

mysqli_close($connection);
?>
