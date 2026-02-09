<?php
require 'db.php';
// map waar images komen
$uploadDir = 'uploads/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // extensie bepalen
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

    // unieke bestandsnaam maken
    $fileName = uniqid() . '.' . $ext;

    // volledig pad
    $filePath = $uploadDir . $fileName;

    // bestand opslaan
    if (move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {
        // filepath opslaan in database
        $stmt = $pdo->prepare(
            "INSERT INTO products (image) VALUES (?)"
        );
        $stmt->execute([$filePath]);

        echo "Upload gelukt 👍";
    } else {
        echo "Upload mislukt ❌";
    }
}
