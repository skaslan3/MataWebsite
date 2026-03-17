<?php
require 'db.php';

$uploadDir = 'uploads/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $merk  = $_POST['merk'];
    $model = $_POST['model'];
    $prijs = $_POST['prijs'];

    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '.' . $ext;
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {

        $stmt = $pdo->prepare(
            "INSERT INTO images (merk, model, prijs, image)
             VALUES (?, ?, ?, ?)"
        );

        $stmt->execute([$merk, $model, $prijs, $filePath]);

        echo "Upload gelukt 👍";

    } else {
        echo "Upload mislukt ❌";
    }
}
