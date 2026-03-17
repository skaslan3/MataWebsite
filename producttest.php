<?php
require 'db.php';

$stmt = $pdo->query("SELECT * FROM images");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Producten</title>
</head>
<body>

<?php if (empty($products)): ?>
    <p>Geen producten gevonden</p>
<?php endif; ?>

<?php foreach ($products as $row): ?>
    <div class="product">
        <img src="<?= $row['image'] ?>" width="200" alt="product">
        <h3><?= htmlspecialchars($row['merk']) ?> - <?= htmlspecialchars($row['model']) ?></h3>
        <p>€<?= number_format($row['prijs'], 2, ',', '.') ?></p>
    </div>
<?php endforeach; ?>

</body>
</html>
