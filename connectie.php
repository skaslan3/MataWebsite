<?php
// Databaseverbinding
$host = "localhost";
$user = "root";
$pass = "";
$db   = "mata_computers";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connectie mislukt: " . $conn->connect_error);
}
?>
