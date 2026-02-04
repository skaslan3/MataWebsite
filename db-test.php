<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Gebruik de gedeelde databaseverbinding
require 'db.php';

try {
    // Let op: de tabel heet 'products', niet 'producten'
    $stmt = $pdo->query("SELECT * FROM products LIMIT 20");
    $producten = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Query-fout: ' . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <title>DB test – producten</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; padding: 2rem; }
        table { border-collapse: collapse; width: 100%; max-width: 900px; }
        th, td { border: 1px solid #ddd; padding: 0.5rem 0.75rem; }
        th { background: #f3f4f6; text-align: left; }
        tr:nth-child(even) { background: #f9fafb; }
        h1 { margin-bottom: 1rem; }
        .empty { color: #6b7280; margin-top: 1rem; }
    </style>
</head>
<body>
<h1>Producten uit de database (testpagina)</h1>

<?php if (count($producten) === 0): ?>
    <p class="empty">
        Er zijn (nog) geen rijen gevonden in de tabel <code>products</code>.<br>
        Controleer in phpMyAdmin of er data in de tabel staat.
    </p>
<?php else: ?>
    <table>
        <thead>
        <tr>
            <?php foreach (array_keys($producten[0]) as $kolom): ?>
                <th><?= htmlspecialchars($kolom) ?></th>
            <?php endforeach; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($producten as $product): ?>
            <tr>
                <?php foreach ($product as $waarde): ?>
                    <td><?= htmlspecialchars($waarde) ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<p class="empty">
    Werkt dit? Mooi! Dan kunnen we ditzelfde principe in je echte pagina's inbouwen.
</p>
</body>
</html>