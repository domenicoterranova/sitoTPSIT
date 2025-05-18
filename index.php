<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AudioWear - Dispositivi Audio Indossabili</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php
// Verifica se la sessione è già attiva
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/products.php';
require_once 'includes/cart.php';
    
    // Ottieni informazioni sul carrello
    $cart = null;
    if (isLoggedIn()) {
        $cart = getCurrentCart();
    }
    
    // Ottieni tutte le categorie per il menu
    $categories = getAllCategories();
    $features = getAllFeatures();
    ?>

    <!-- Header -->
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
                                    <a class="dropdown-item" href="products.php?category=<?= $category['category_id'] ?>">
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
                                    <a class="dropdown-item" href="products.php?feature=<?= $feature['feature_id'] ?>">
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

    <!-- Contenuto principale -->
    <main class="container my-4">
        <!-- Carosello in evidenza -->
        <div id="featuredCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#featuredCarousel" data-bs-slide-to="0" class="active"></button>
                <button type="button" data-bs-target="#featuredCarousel" data-bs-slide-to="1"></button>
                <button type="button" data-bs-target="#featuredCarousel" data-bs-slide-to="2"></button>
            </div>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="assets/images/banner1.jpg" class="d-block w-100" alt="AudioWear Pro Max">
                    <div class="carousel-caption d-none d-md-block">
                        <h2>AudioWear Pro Max</h2>
                        <p>Cuffie premium con monitoraggio del battito cardiaco</p>
                        <a href="product.php?id=1" class="btn btn-primary">Scopri di più</a>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="assets/images/banner2.jpg" class="d-block w-100" alt="AudioWear FitBuds">
                    <div class="carousel-caption d-none d-md-block">
                        <h2>AudioWear FitBuds</h2>
                        <p>Auricolari sportivi con contapassi integrato</p>
                        <a href="product.php?id=2" class="btn btn-primary">Scopri di più</a>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="assets/images/banner3.jpg" class="d-block w-100" alt="AudioWear SleepPods">
                    <div class="carousel-caption d-none d-md-block">
                        <h2>AudioWear SleepPods</h2>
                        <p>Monitoraggio del sonno e audio rilassante</p>
                        <a href="product.php?id=3" class="btn btn-primary">Scopri di più</a>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#featuredCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Precedente</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#featuredCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Successivo</span>
            </button>
        </div>

        <!-- Categorie in evidenza -->
        <section class="mb-5">
            <h2 class="text-center mb-4">Categorie</h2>
            <div class="row">
                <?php foreach ($categories as $category): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <h3 class="card-title"><?= htmlspecialchars($category['nome']) ?></h3>
                            <p class="card-text"><?= htmlspecialchars($category['descrizione']) ?></p>
                            <a href="products.php?category=<?= $category['id'] ?>" class="btn btn-primary">Esplora</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Prodotti in evidenza -->
        <section class="mb-5">
            <h2 class="text-center mb-4">Prodotti in evidenza</h2>
            <div class="row">
                <?php 
                $featuredProducts = getAllProducts(4); // Ottieni 4 prodotti recenti
                foreach ($featuredProducts as $product): 
                ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <img src="<?= htmlspecialchars($product['immagine']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['nome']) ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($product['nome']) ?></h5>
                            <p class="card-text flex-grow-1"><?= htmlspecialchars(substr($product['descrizione'], 0, 80)) ?>...</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h5 mb-0">€<?= number_format($product['prezzo'], 2) ?></span>
                                <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-outline-primary">Dettagli</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-3">
                <a href="products.php" class="btn btn-primary">Vedi tutti i prodotti</a>
            </div>
        </section>

        <!-- Funzionalità -->
        <section class="mb-5">
            <h2 class="text-center mb-4">Caratteristiche speciali</h2>
            <div class="row">
                <div class="col-md-3 mb-4 text-center">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Wireless</h5>
                            <p class="card-text">Connessione senza fili per libertà di movimento</p>
                            <a href="products.php?wireless=1" class="btn btn-sm btn-outline-primary">Prodotti wireless</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4 text-center">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Noise Cancelling</h5>
                            <p class="card-text">Tecnologia di cancellazione del rumore</p>
                            <a href="products.php?noise_cancelling=1" class="btn btn-sm btn-outline-primary">Prodotti con noise cancelling</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4 text-center">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Microfono</h5>
                            <p class="card-text">Dispositivi con microfono integrato</p>
                            <a href="products.php?microfono=1" class="btn btn-sm btn-outline-primary">Prodotti con microfono</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4 text-center">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Resistenti all'acqua</h5>
                            <p class="card-text">Dispositivi impermeabili o resistenti agli schizzi</p>
                            <a href="products.php?resistente_acqua=1" class="btn btn-sm btn-outline-primary">Prodotti resistenti all'acqua</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <h5>AudioWear</h5>
                    <p>La tecnologia indossabile per la tua musica</p>
                </div>
                <div class="col-md-3 mb-3">
                    <h5>Collegamenti rapidi</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-decoration-none text-white">Home</a></li>
                        <li><a href="products.php" class="text-decoration-none text-white">Prodotti</a></li>
                        <li><a href="about.php" class="text-decoration-none text-white">Chi siamo</a></li>
                        <li><a href="contact.php" class="text-decoration-none text-white">Contatti</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-3">
                    <h5>Categorie</h5>
                    <ul class="list-unstyled">
                        <?php foreach ($categories as $category): ?>
                        <li>
                            <a href="products.php?category=<?= $category['id'] ?>" class="text-decoration-none text-white">
                                <?= htmlspecialchars($category['nome']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-md-3 mb-3">
                    <h5>Seguici</h5>
                    <div class="d-flex gap-3 fs-5">
                        <a href="#" class="text-white"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>&copy; <?= date('Y') ?> AudioWear. Tutti i diritti riservati.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>