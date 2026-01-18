<?php


$host = 'localhost';
$dbname = 'dziennik_szkolny'; // Nazwa bazy
$username = 'root';            // Domyślny użytkownik XAMPP
$password = '';                // Domyślne hasło XAMPP 

try {
    // Tworzenie połączenia przy użyciu biblioteki PDO 
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    
    die("Błąd połączenia z bazą danych: " . $e->getMessage());
}
?>