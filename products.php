<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/products.php';
require_once 'includes/cart.php';

// Ottieni informazioni sul carrello
$cart = null;
if (isLoggedIn()) {
    $cart = getCurrentCart();
}

// Ottieni parametri di ricerca
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
$featureId = isset($_GET['feature']) ? (int)$_GET['feature'] : null;
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : null;

// Ottieni prodotti in base ai parametri
$products = [];
$pageTitle = "Tutti i prodotti";

if ($searchQuery) {
    $products = searchProducts($searchQuery);
    $pageTitle = "Risultati per: " . htmlspecialchars($searchQuery);
} elseif ($categoryId) {
    $products = getAllProducts(null, $categoryId);
    $categories = getAllCategories();
    foreach ($categories as $category) {
        if ($category['category_id'] == $categoryId) {
            $pageTitle = "Categoria: " . htmlspecialchars($category['name']);
            break;
        }
    }
} elseif ($featureId) {
    $products = getAllProducts(null, null, $featureId);
    $features = getAllFeatures();
    foreach ($features as $feature) {
        if ($feature['feature_id'] == $featureId) {
            $pageTitle = "Caratteristica: " . htmlspecialchars($feature['name']);
            break;
        }
    }
} else {
    $products = getAllProducts();
}

// Ottieni tutte le categorie e caratteristiche per i filtri
$allCategories = getAllCategories();
$allFeatures = getAllFeatures();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - AudioWear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container my-4">
        <div class="row">
            <!-- Filtri laterali -->
            <div class="col-lg-3 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Filtri</h4>
                    </div>
                    <div class="card-body">
                        <h5>Categorie</h5>
                        <ul class="list-group mb-3">
                            <li class="list-group-item <?= !$categoryId ? 'active' : '' ?>">
                                <a href="products.php" class="text-decoration-none <?= !$categoryId ? 'text-white' : 'text-dark' ?>">
                                    Tutte le categorie
                                </a>
                            </li>
                            <?php foreach ($allCategories as $category): ?>
                            <li class="list-group-item <?= $categoryId == $category['category_id'] ? 'active' : '' ?>">
                                <a href="products.php?category=<?= $category['category_id'] ?>" class="text-decoration-none <?= $categoryId == $category['category_id'] ? 'text-white' : 'text-dark' ?>">
                                    <?= htmlspecialchars($category['name']) ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <h5>Caratteristiche</h5>
                        <ul class="list-group">
                            <?php foreach ($allFeatures as $feature): ?>
                            <li class="list-group-item <?= $featureId == $feature['feature_id'] ? 'active' : '' ?>">
                                <a href="products.php?feature=<?= $feature['feature_id'] ?>" class="text-decoration-none <?= $featureId == $feature['feature_id'] ? 'text-white' : 'text-dark' ?>">
                                    <?= htmlspecialchars($feature['name']) ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Lista prodotti -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><?= $pageTitle ?></h1>
                    <span><?= count($products) ?> prodotti trovati</span>
                </div>
                
                <?php if (empty($products)): ?>
                    <div class="alert alert-info">
                        Nessun prodotto trovato. Prova a modificare i filtri o la ricerca.
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($products as $product): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <img src="<?= htmlspecialchars($product['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                                    <p class="card-text flex-grow-1"><?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="h5 mb-0">€<?= number_format($product['price'], 2) ?></span>
                                        <a href="product.php?id=<?= $product['product_id'] ?>" class="btn btn-outline-primary">Dettagli</a>
                                    </div>
                                </div>
                                <div class="card-footer d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <?= htmlspecialchars($product['category_name']) ?>
                                    </small>
                                    <?php if (!empty($product['features'])): ?>
                                    <div>
                                        <?php foreach ($product['features'] as $feature): ?>
                                        <span class="badge bg-secondary" title="<?= htmlspecialchars($feature['description']) ?>">
                                            <?= htmlspecialchars($feature['name']) ?>
                                        </span>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>