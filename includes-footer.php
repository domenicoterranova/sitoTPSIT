<?php
// File: includes/footer.php
// Questo file viene incluso in tutte le pagine per visualizzare il footer del sito

// Verifica che le funzioni necessarie siano disponibili
if (!function_exists('getAllCategories')) {
    require_once 'includes/products.php';
}

// Ottieni le categorie per il footer se non già disponibili
if (!isset($categories)) {
    $categories = getAllCategories();
}
?>

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