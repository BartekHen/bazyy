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

// Fetch all users with plaintext passwords
$query = "SELECT id_uzytkownika, haslo FROM uzytkownik";
$result = mysqli_query($connection, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $userId = $row['id_uzytkownika'];
    $plainPassword = $row['haslo'];

    // Hash the plaintext password
    $hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);

    // Update the user's password in the database
    $updateQuery = "UPDATE uzytkownik SET haslo='$hashedPassword' WHERE id_uzytkownika=$userId";
    mysqli_query($connection, $updateQuery);
}

echo "Password migration completed!";
mysqli_close($connection);
?>