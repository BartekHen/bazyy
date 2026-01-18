<?php
session_start();
session_destroy(); // Niszczy sesję
header("Location: index.php"); // Odsyła do logowania
exit;
?>