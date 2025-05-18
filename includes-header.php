<?php
// File: includes/header.php
// Questo file viene incluso in tutte le pagine per visualizzare l'header del sito

// Verifica che le funzioni necessarie siano disponibili
if (!function_exists('getAllCategories')) {
    require_once 'includes/products.php';
}

// Ottieni tutte le categorie per il menu
$categories = getAllCategories();
// Ottieni caratteristiche fittizie per il menu
$features = getAllFeatures();
?>

<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">AudioWear</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button" data-bs-toggle="dropdown">
                            Categorie
                        </a>
                        <ul class="dropdown-menu">
                            <?php foreach ($categories as $category): ?>
                            <li>
                                <a class="dropdown-item" href="products.php?category=<?= $category['id'] ?>">
                                    <?= htmlspecialchars($category['nome']) ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="featuresDropdown" role="button" data-bs-toggle="dropdown">
                            Caratteristiche
                        </a>
                        <ul class="dropdown-menu">
                            <?php foreach ($features as $feature): ?>
                            <li>
                                <a class="dropdown-item" href="products.php?feature=<?= $feature['id'] ?>">
                                    <?= htmlspecialchars($feature['nome']) ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                </ul>
                
                <!-- Barra di ricerca -->
                <form class="d-flex mx-auto" action="products.php" method="GET">
                    <div class="input-group">
                        <input class="form-control" type="search" name="search" placeholder="Cerca prodotti..." aria-label="Search">
                        <button class="btn btn-outline-light" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                
                <ul class="navbar-nav ms-auto">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="wishlist.php">
                                <i class="fas fa-heart"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="cart.php">
                                <i class="fas fa-shopping-cart"></i>
                                <?php if ($cart && $cart['total_quantity'] > 0): ?>
                                    <span class="badge bg-danger"><?= $cart['total_quantity'] ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profile.php">Profilo</a></li>
                                <li><a class="dropdown-item" href="orders.php">I miei ordini</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Accedi</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Registrati</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>