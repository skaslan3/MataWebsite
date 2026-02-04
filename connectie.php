<?php
// Databaseverbinding met betere error handling
error_reporting(E_ALL);
ini_set('display_errors', 0); // Zet op 1 voor debugging, maar 0 in productie

$host = "localhost";
$user = "root";
$pass = "";
$db   = "mata_computers";

// Probeer verbinding te maken
$conn = @new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    // In plaats van direct te stoppen, kunnen we een betere foutmelding geven
    die("Database-verbinding mislukt: " . htmlspecialchars($conn->connect_error) . 
        "<br><br>Controleer:<br>" .
        "1. Draait MySQL in XAMPP/WAMP/Laragon?<br>" .
        "2. Bestaat de database '{$db}' in phpMyAdmin?<br>" .
        "3. Zijn de credentials correct?");
}

// Zet charset naar UTF-8 voor Nederlandse tekens
$conn->set_charset("utf8mb4");
?>
