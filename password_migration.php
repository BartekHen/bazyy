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

// Helper function to check if a password is already hashed
function isPasswordHashed($password) {
    return preg_match('/^\$2[ayb]\$\d{2}\$/', $password) === 1;
}

// Fetch all users
$query = "SELECT id_uzytkownika, haslo FROM uzytkownik";
$result = mysqli_query($connection, $query);

$updated = 0;
$skipped = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $userId = $row['id_uzytkownika'];
    $password = $row['haslo'];
    
    // Check if password is already hashed
    if (isPasswordHashed($password)) {
        $skipped++;
        continue;
    }

    // Hash the plaintext password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Use prepared statement to update the password
    $stmt = mysqli_prepare($connection, "UPDATE uzytkownik SET haslo = ? WHERE id_uzytkownika = ?");
    mysqli_stmt_bind_param($stmt, "si", $hashedPassword, $userId);
    
    if (mysqli_stmt_execute($stmt)) {
        $updated++;
    }
    
    mysqli_stmt_close($stmt);
}

echo "Password migration completed!\n";
echo "Updated: $updated passwords\n";
echo "Skipped: $skipped already hashed passwords\n";
mysqli_close($connection);
?>