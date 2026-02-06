<?php
require 'db.php';

// Pas deze kolomnamen aan naar jouw eigen database-structuur
// Voorbeeld: products (id, name, price, description, image_url, category, created_at)
$kolomId          = 'id';
$kolomNaam        = 'name';
$kolomPrijs       = 'price';
$kolomOmschrijving = 'description';
$kolomAfbeelding  = 'image_url';
$kolomCategorie   = 'category';   // bv. 'Telefoons', 'Tablets', 'Laptops', 'Accessoires'
$kolomAangemaakt  = 'created_at'; // of laat op 'id' als je geen datumkolom hebt

// URL-parameters voor filtering & sortering
$selectedCatKey = isset($_GET['cat']) ? strtolower(trim($_GET['cat'])) : null;
$selectedSort   = isset($_GET['sort']) ? $_GET['sort'] : 'default';

// Map van URL-sleutels naar categorie-waarden in de database
// Pas de rechterkant aan naar de exacte waardes in jouw 'category'-kolom.
$categorieMap = [
    'telefoons'   => 'Telefoons',
    'tablets'     => 'Tablets',
    'laptops'     => 'Laptops',
    'accessoires' => 'Accessoires',
];

$whereSql  = '';
$params    = [];
$catLabel  = 'Alle producten';

if ($selectedCatKey && isset($categorieMap[$selectedCatKey])) {
    $whereSql         = "WHERE `$kolomCategorie` = :cat";
    $params[':cat']   = $categorieMap[$selectedCatKey];
    $catLabel         = $categorieMap[$selectedCatKey];
}

// Sortering veilig bepalen (alleen whitelisted opties)
switch ($selectedSort) {
    case 'price_asc':
        $orderSql = "ORDER BY `$kolomPrijs` ASC";
        break;
    case 'price_desc':
        $orderSql = "ORDER BY `$kolomPrijs` DESC";
        break;
    case 'newest':
        $kolomVoorDatum = $kolomAangemaakt ?: $kolomId;
        $orderSql = "ORDER BY `$kolomVoorDatum` DESC";
        break;
    default:
        $orderSql = "ORDER BY `$kolomNaam` ASC";
        $selectedSort = 'default';
}

// Query opbouwen
$sql = "SELECT * FROM products $whereSql $orderSql LIMIT 60";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $producten = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Query-fout: ' . htmlspecialchars($e->getMessage()) . '<br>SQL: ' . htmlspecialchars($sql));
}
?>
<!DOCTYPE html>
<html class="light" lang="nl">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Producten Overzicht - Mata Computers</title>
    <!-- Google Fonts & Icons -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- Theme Config -->
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#137fec",
                        "background-light": "#f6f7f8",
                        "background-dark": "#101922",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 flex flex-col min-h-screen">
<!-- Top Navigation (gedeeld met homepage) -->
<div class="w-full bg-white dark:bg-[#1a2632] border-b border-[#e7edf3] dark:border-[#2a3b4d] sticky top-0 z-50">
    <div class="px-6 md:px-10 py-3 max-w-[1440px] mx-auto w-full">
        <header class="flex items-center justify-between whitespace-nowrap">

            <a href="index.html" class="flex items-center gap-3">
                <img src="images/mata-logo.png" alt="Mata Computers Logo" class="h-12 w-auto">
            </a>

            <!-- Navigation + buttons -->
            <div class="flex items-center gap-4 lg:gap-8">
                <!-- Nav links -->
                <nav class="hidden lg:flex items-center gap-3">
                    <div class="group relative">
                        <a href="Producten.php" class="text-sm font-medium text-[#0d141b] dark:text-gray-200 hover:text-primary dark:hover:text-primary py-2 px-4 rounded-lg hover:bg-gray-100 dark:hover:bg-[#0d1419] transition-colors inline-block">
                            Producten
                        </a>

                        <div class="absolute left-0 mt-2 w-48 bg-white dark:bg-[#1a2632] shadow-xl rounded-xl border border-[#e7edf3] dark:border-[#2a3b4d]
                                    opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all overflow-hidden">
                            <a href="Product1.html" class="block px-4 py-3 text-sm text-[#0d141b] dark:text-gray-200 hover:bg-primary hover:text-white transition-colors border-b border-[#e7edf3] dark:border-[#2a3b4d] last:border-b-0">
                                Telefoons
                            </a>
                            <a href="Product2.html" class="block px-4 py-3 text-sm text-[#0d141b] dark:text-gray-200 hover:bg-primary hover:text-white transition-colors border-b border-[#e7edf3] dark:border-[#2a3b4d] last:border-b-0">
                                Tablets & iPads
                            </a>
                            <a href="Product3.html" class="block px-4 py-3 text-sm text-[#0d141b] dark:text-gray-200 hover:bg-primary hover:text-white transition-colors border-b border-[#e7edf3] dark:border-[#2a3b4d] last:border-b-0">
                                Laptops
                            </a>
                            <a href="Product3.html" class="block px-4 py-3 text-sm text-[#0d141b] dark:text-gray-200 hover:bg-primary hover:text-white transition-colors">
                                Accessoires
                            </a>
                        </div>
                    </div>

                    <a href="Reparaties.html" class="text-sm font-medium text-[#0d141b] dark:text-gray-200 hover:text-primary dark:hover:text-primary py-2 px-4 rounded-lg hover:bg-gray-100 dark:hover:bg-[#0d1419] transition-colors">Reparaties</a>
                    <a href="Contact.html" class="text-sm font-medium text-[#0d141b] dark:text-gray-200 hover:text-primary dark:hover:text-primary py-2 px-4 rounded-lg hover:bg-gray-100 dark:hover:bg-[#0d1419] transition-colors">Over ons</a>
                </nav>

                <!-- Buttons -->
                <div class="flex gap-2">
                    <a href="Reparaties.html#afspraak" class="hidden md:flex">
                        <button class="h-10 px-4 bg-primary text-white rounded-lg font-bold text-sm">
                            Maak Afspraak
                        </button>
                    </a>

                    <!-- Dark mode toggle button -->
                    <button
                            id="dark-mode-toggle"
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            aria-label="Toggle dark mode"
                    >
                        <span class="material-symbols-outlined dark:hidden">dark_mode</span>
                        <span class="material-symbols-outlined hidden dark:inline">light_mode</span>
                    </button>

                    <!-- Mobile menu button -->
                    <button
                            id="menu-btn"
                            class="lg:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                </div>

            </div>
        </header>
    </div>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="hidden lg:hidden px-6 pb-6 pt-4 border-t border-[#e7edf3] dark:border-[#2a3b4d] bg-white dark:bg-[#1a2632]">
        <nav class="flex flex-col gap-2">
            <a href="Producten.php" class="font-medium text-[#0d141b] dark:text-gray-200 py-3 px-4 rounded-lg hover:bg-gray-100 dark:hover:bg-[#0d1419] transition-colors border-b border-[#e7edf3] dark:border-[#2a3b4d]">
                Producten
            </a>
            <a href="Reparaties.html" class="font-medium text-[#0d141b] dark:text-gray-200 py-3 px-4 rounded-lg hover:bg-gray-100 dark:hover:bg-[#0d1419] transition-colors border-b border-[#e7edf3] dark:border-[#2a3b4d]">
                Reparaties
            </a>
            <a href="Contact.html" class="font-medium text-[#0d141b] dark:text-gray-200 py-3 px-4 rounded-lg hover:bg-gray-100 dark:hover:bg-[#0d1419] transition-colors border-b border-[#e7edf3] dark:border-[#2a3b4d]">
                Over ons
            </a>
            <a href="Reparaties.html#afspraak" class="font-bold text-white bg-primary hover:bg-blue-600 py-3 px-4 rounded-lg transition-colors mt-2 text-center shadow-md">
                Maak Afspraak
            </a>
        </nav>
    </div>
</div>
<!-- Main Content -->
<main class="flex-grow w-full max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumbs & Header -->
    <div class="mb-8">
        <nav class="flex mb-4 text-sm text-slate-500 dark:text-slate-400">
            <a class="hover:text-primary transition-colors" href="index.html">Home</a>
            <span class="mx-2">/</span>
            <span class="text-slate-900 dark:text-white font-medium">Producten</span>
        </nav>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h1 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-white tracking-tight">
                <?php echo htmlspecialchars($catLabel); ?>
            </h1>
            <div class="flex items-center gap-3">
                <span class="text-sm text-slate-500 dark:text-slate-400">
                    <?php echo count($producten); ?> resultaten
                </span>
                <form method="get" class="inline-flex items-center">
                    <?php if ($selectedCatKey): ?>
                        <input type="hidden" name="cat" value="<?php echo htmlspecialchars($selectedCatKey); ?>">
                    <?php endif; ?>
                    <select
                            name="sort"
                            class="form-select bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm py-2 pl-3 pr-8 focus:border-primary focus:ring-primary dark:text-white cursor-pointer shadow-sm"
                            onchange="this.form.submit()"
                    >
                        <option value="default" <?php echo $selectedSort === 'default' ? 'selected' : ''; ?>>Aanbevolen</option>
                        <option value="price_asc" <?php echo $selectedSort === 'price_asc' ? 'selected' : ''; ?>>Prijs: Laag naar Hoog</option>
                        <option value="price_desc" <?php echo $selectedSort === 'price_desc' ? 'selected' : ''; ?>>Prijs: Hoog naar Laag</option>
                        <option value="newest" <?php echo $selectedSort === 'newest' ? 'selected' : ''; ?>>Nieuwste eerst</option>
                    </select>
                </form>
            </div>
        </div>
    </div>
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Filters (zoals in je HTML, nu statisch) -->
        <aside class="w-full lg:w-64 flex-shrink-0 space-y-8">
            <!-- Mobile Filter Toggle (Visible only on small screens) -->
            <div class="lg:hidden">
                <button class="flex items-center justify-center w-full gap-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 py-2.5 rounded-lg font-medium text-slate-700 dark:text-slate-200">
                    <span class="material-symbols-outlined text-[20px]">tune</span>
                    Filters tonen
                </button>
            </div>
            <!-- Filters Container -->
            <div class="hidden lg:block space-y-8">
                <!-- Prijsklasse etc. (overgenomen uit je HTML, ingekort) -->
                <div class="space-y-3">
                    <h3 class="font-bold text-slate-900 dark:text-white">Prijsklasse</h3>
                    <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm text-slate-500 dark:text-slate-400">€0</span>
                            <span class="text-sm text-slate-500 dark:text-slate-400">€2000+</span>
                        </div>
                        <div class="relative h-2 bg-slate-200 dark:bg-slate-700 rounded-full mb-4">
                            <div class="absolute left-[10%] right-[30%] top-0 bottom-0 bg-primary rounded-full"></div>
                            <div class="absolute left-[10%] top-1/2 -translate-y-1/2 w-4 h-4 bg-white border-2 border-primary rounded-full shadow cursor-pointer hover:scale-110 transition-transform"></div>
                            <div class="absolute right-[30%] top-1/2 -translate-y-1/2 w-4 h-4 bg-white border-2 border-primary rounded-full shadow cursor-pointer hover:scale-110 transition-transform"></div>
                        </div>
                        <div class="flex gap-2">
                            <div class="relative w-full">
                                <span class="absolute left-3 top-2.5 text-xs text-slate-400">Min</span>
                                <input class="w-full pl-3 pt-5 pb-1 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-primary focus:border-primary dark:text-white" type="number" value="200"/>
                            </div>
                            <div class="relative w-full">
                                <span class="absolute left-3 top-2.5 text-xs text-slate-400">Max</span>
                                <input class="w-full pl-3 pt-5 pb-1 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-primary focus:border-primary dark:text-white" type="number" value="1400"/>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- (andere filters zoals categorie/merk/conditie kun je hier laten staan zoals in de HTML) -->
            </div>
        </aside>

        <!-- Product Grid Area -->
        <section class="flex-1">
            <!-- Active Filters (Chips) -->
            <div class="flex flex-wrap gap-2 mb-6">
                <?php if ($selectedCatKey && isset($categorieMap[$selectedCatKey])): ?>
                    <span class="inline-flex items-center gap-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full px-3 py-1 text-sm text-slate-700 dark:text-slate-300">
                        <?php echo htmlspecialchars($categorieMap[$selectedCatKey]); ?>
                    </span>
                <?php endif; ?>
                <?php if ($selectedSort !== 'default'): ?>
                    <span class="inline-flex items-center gap-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full px-3 py-1 text-sm text-slate-700 dark:text-slate-300">
                        <?php
                        $labels = [
                            'price_asc'  => 'Prijs ↑',
                            'price_desc' => 'Prijs ↓',
                            'newest'     => 'Nieuwste eerst',
                        ];
                        echo htmlspecialchars($labels[$selectedSort] ?? 'Sortering');
                        ?>
                    </span>
                <?php endif; ?>
                <?php if ($selectedCatKey || $selectedSort !== 'default'): ?>
                    <a href="Producten.php" class="text-sm text-primary font-medium hover:underline ml-2">Wis alles</a>
                <?php endif; ?>
            </div>

            <?php if (empty($producten)): ?>
                <p class="text-slate-500 dark:text-slate-400">
                    Er zijn nog geen producten gevonden in de tabel <code>products</code>.
                    Voeg producten toe in phpMyAdmin of pas de query bovenaan <code>Producten.php</code> aan.
                </p>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    <?php foreach ($producten as $product): ?>
                        <div class="group relative bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col">
                            <div class="relative aspect-[4/3] bg-slate-100 dark:bg-slate-900 overflow-hidden p-6 flex items-center justify-center">
                                <!-- Badge (optioneel) -->
                                <div class="absolute top-3 left-3 z-10">
                                    <span class="bg-blue-600 text-white text-xs font-bold px-2.5 py-1 rounded-md uppercase tracking-wider">Product</span>
                                </div>
                                <!-- Image -->
                                <?php if (!empty($product[$kolomAfbeelding] ?? '')): ?>
                                    <img class="object-contain h-full w-full group-hover:scale-105 transition-transform duration-500"
                                         src="<?php echo htmlspecialchars($product[$kolomAfbeelding]); ?>"
                                         alt="<?php echo htmlspecialchars($product[$kolomNaam] ?? 'Product'); ?>">
                                <?php else: ?>
                                    <span class="text-slate-400 text-sm">Geen afbeelding</span>
                                <?php endif; ?>
                            </div>
                            <div class="p-5 flex-1 flex flex-col">
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="text-lg font-bold text-slate-900 dark:text-white line-clamp-2 group-hover:text-primary transition-colors">
                                        <?php echo htmlspecialchars($product[$kolomNaam] ?? 'Naam onbekend'); ?>
                                    </h3>
                                </div>
                                <?php if (!empty($product[$kolomOmschrijving] ?? '')): ?>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-3 line-clamp-2">
                                        <?php echo htmlspecialchars($product[$kolomOmschrijving]); ?>
                                    </p>
                                <?php endif; ?>
                                <div class="mt-auto flex items-end justify-between">
                                    <div class="flex flex-col">
                                        <span class="text-xs text-slate-500 dark:text-slate-400">Prijs</span>
                                        <span class="text-xl font-bold text-slate-900 dark:text-white">
                                            €<?php
                                            $prijs = $product[$kolomPrijs] ?? 0;
                                            if (is_numeric($prijs)) {
                                                echo htmlspecialchars(number_format((float)$prijs, 2, ',', '.'));
                                            } else {
                                                echo htmlspecialchars($prijs);
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    <button class="bg-primary/10 hover:bg-primary text-primary hover:text-white p-2.5 rounded-lg transition-colors">
                                        <span class="material-symbols-outlined text-[20px] block">add_shopping_cart</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<!-- Banner / Repair CTA (overgenomen uit HTML) -->
<section class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 py-12">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-primary/5 dark:bg-primary/10 rounded-3xl p-8 md:p-12 flex flex-col md:flex-row items-center justify-between gap-8 border border-primary/20">
            <div class="max-w-2xl">
                <h2 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white mb-4">Is je huidige telefoon stuk?</h2>
                <p class="text-slate-600 dark:text-slate-300 text-lg mb-6">Wij repareren schermen, batterijen en meer. Vaak al klaar terwijl je wacht!</p>
                <div class="flex gap-4">
                    <a href="Reparaties.html#afspraak" class="bg-primary text-white px-6 py-3 rounded-lg font-bold hover:bg-blue-600 transition-colors shadow-lg shadow-primary/30 text-center">
                        Reparatie aanmelden
                    </a>
                    <a href="Reparaties.html#prijzen" class="text-primary font-bold px-6 py-3 rounded-lg hover:bg-primary/10 transition-colors text-center">
                        Bekijk prijzen
                    </a>
                </div>
            </div>
            <div class="hidden md:block">
                <span class="material-symbols-outlined text-[120px] text-primary/40">build_circle</span>
            </div>
        </div>
    </div>
</section>

<!-- Simple Footer -->
<footer class="bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 py-12">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
        <div class="flex items-center gap-2">
            <div class="size-6 text-slate-400 flex items-center justify-center">
                <span class="material-symbols-outlined text-xl">devices</span>
            </div>
            <span class="text-slate-500 font-medium">© 2024 Mata Computers. Alle rechten voorbehouden.</span>
        </div>
        <div class="flex gap-6">
            <a class="text-slate-500 hover:text-primary transition-colors" href="#">Privacy</a>
            <a class="text-slate-500 hover:text-primary transition-colors" href="#">Voorwaarden</a>
            <a class="text-slate-500 hover:text-primary transition-colors" href="Contact.html">Contact</a>
        </div>
    </div>
</footer>

<!-- Gedeelde JavaScript voor hamburger-menu en dark mode -->
<script src="main.js"></script>

</body>
</html>
