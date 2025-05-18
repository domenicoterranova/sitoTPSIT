<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/products.php';
require_once 'includes/cart.php';
require_once 'includes/wishlist.php';

// Reindirizza se non loggato
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}

// Ottieni ID prodotto
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($productId <= 0) {
    header("Location: products.php");
    exit();
}

// Ottieni dettagli prodotto
$product = getProductById($productId);
if (!$product) {
    header("Location: products.php");
    exit();
}

// Controlla se il prodotto è nella wishlist
$inWishlist = isInWishlist($productId);

// Elabora azioni
$message = null;
$messageType = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_cart'])) {
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        $result = addToCart($productId, $quantity);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    } elseif (isset($_POST['add_to_wishlist'])) {
        $result = addToWishlist($productId);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
        if ($result['success']) {
            $inWishlist = true;
        }
    } elseif (isset($_POST['remove_from_wishlist'])) {
        // Ottieni l'ID della wishlist
        $conn = connectDB();
        $stmt = $conn->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $userId = $_SESSION['user_id'];
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $wishlistItem = $result->fetch_assoc();
            $wishlistId = $wishlistItem['wishlist_id'];
            $stmt->close();
            $conn->close();
            
            $result = removeFromWishlist($wishlistId);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'danger';
            if ($result['success']) {
                $inWishlist = false;
            }
        } else {
            $stmt->close();
            $conn->close();
        }
    } elseif (isset($_POST['add_review'])) {
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
        $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
        
        if ($rating < 1 || $rating > 5) {
            $message = "Valutazione non valida. Deve essere compresa tra 1 e 5.";
            $messageType = 'danger';
        } else {
            $conn = connectDB();
            $userId = $_SESSION['user_id'];
            
            // Verifica se l'utente ha già recensito questo prodotto
            $checkStmt = $conn->prepare("SELECT review_id FROM reviews WHERE user_id = ? AND product_id = ?");
            $checkStmt->bind_param("ii", $userId, $productId);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows > 0) {
                // Aggiorna la recensione esistente
                $reviewData = $result->fetch_assoc();
                $reviewId = $reviewData['review_id'];
                $checkStmt->close();
                
                $updateStmt = $conn->prepare("UPDATE reviews SET rating = ?, comment = ?, created_at = CURRENT_TIMESTAMP WHERE review_id = ?");
                $updateStmt->bind_param("isi", $rating, $comment, $reviewId);
                $success = $updateStmt->execute();
                $updateStmt->close();
            } else {
                // Crea una nuova recensione
                $checkStmt->close();
                
                $insertStmt = $conn->prepare("INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)");
                $insertStmt->bind_param("iiis", $userId, $productId, $rating, $comment);
                $success = $insertStmt->execute();
                $insertStmt->close();
            }
            
            $conn->close();
            
            if ($success) {
                $message = "Recensione pubblicata con successo.";
                $messageType = 'success';
                
                // Aggiorna il prodotto per visualizzare la nuova recensione
                $product = getProductById($productId);
            } else {
                $message = "Errore durante la pubblicazione della recensione. Riprova più tardi.";
                $messageType = 'danger';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - AudioWear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container my-4">
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-5 mb-4">
                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="img-fluid rounded">
            </div>
            
            <div class="col-md-7">
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                
                <div class="mb-3">
                    <span class="badge bg-primary"><?= htmlspecialchars($product['category_name']) ?></span>
                    <?php foreach ($product['features'] as $feature): ?>
                        <span class="badge bg-secondary" title="<?= htmlspecialchars($feature['description']) ?>"><?= htmlspecialchars($feature['name']) ?></span>
                    <?php endforeach; ?>
                </div>
                
                <?php
                // Calcola valutazione media
                $avgRating = 0;
                $totalReviews = count($product['reviews']);
                if ($totalReviews > 0) {
                    $ratingSum = array_sum(array_column($product['reviews'], 'rating'));
                    $avgRating = $ratingSum / $totalReviews;
                }
                ?>
                
                <div class="mb-3">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?php if ($i <= round($avgRating)): ?>
                            <i class="fas fa-star text-warning"></i>
                        <?php else: ?>
                            <i class="far fa-star text-warning"></i>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <span class="ms-2"><?= $totalReviews ?> recensioni</span>
                </div>
                
                <div class="mb-3">
                    <h2 class="h3 text-primary">€<?= number_format($product['price'], 2) ?></h2>
                    
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <span class="badge bg-success">Disponibile</span>
                        <span class="text-muted"><?= $product['stock_quantity'] ?> in magazzino</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Esaurito</span>
                    <?php endif; ?>
                </div>
                
                <div class="mb-4">
                    <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                </div>
                
                <?php if ($product['stock_quantity'] > 0): ?>
                    <form method="POST" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>" class="mb-3">
                        <div class="row g-3 align-items-center">
                            <div class="col-auto">
                                <label for="quantity" class="form-label">Quantità:</label>
                            </div>
                            <div class="col-2">
                                <input type="number" id="quantity" name="quantity" class="form-control" value="1" min="1" max="<?= $product['stock_quantity'] ?>">
                            </div>
                            <div class="col-auto">
                                <button type="submit" name="add_to_cart" class="btn btn-primary">
                                    <i class="fas fa-cart-plus me-2"></i>Aggiungi al carrello
                                </button>
                            </div>
                            <div class="col-auto">
                                <?php if ($inWishlist): ?>
                                    <button type="submit" name="remove_from_wishlist" class="btn btn-outline-danger">
                                        <i class="fas fa-heart me-2"></i>Rimuovi dalla wishlist
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="add_to_wishlist" class="btn btn-outline-primary">
                                        <i class="far fa-heart me-2"></i>Aggiungi alla wishlist
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning">
                        Questo prodotto è temporaneamente esaurito. Aggiungi alla wishlist per essere notificato quando sarà disponibile.
                    </div>
                    <form method="POST" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>" class="mb-3">
                        <?php if ($inWishlist): ?>
                            <button type="submit" name="remove_from_wishlist" class="btn btn-outline-danger">
                                <i class="fas fa-heart me-2"></i>Rimuovi dalla wishlist
                            </button>
                        <?php else: ?>
                            <button type="submit" name="add_to_wishlist" class="btn btn-outline-primary">
                                <i class="far fa-heart me-2"></i>Aggiungi alla wishlist
                            </button>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
                
                <div class="mb-4">
                    <h4>Caratteristiche Principali</h4>
                    <ul>
                        <?php foreach ($product['features'] as $feature): ?>
                            <li><strong><?= htmlspecialchars($feature['name']) ?>:</strong> <?= htmlspecialchars($feature['description']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-12">
                <h3>Recensioni</h3>
                <hr>
                
                <?php if (empty($product['reviews'])): ?>
                    <div class="alert alert-info">
                        Nessuna recensione disponibile. Sii il primo a recensire questo prodotto!
                    </div>
                <?php else: ?>
                    <?php foreach ($product['reviews'] as $review): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="card-title"><?= htmlspecialchars($review['username']) ?></h5>
                                    <small class="text-muted"><?= date('d/m/Y', strtotime($review['created_at'])) ?></small>
                                </div>
                                
                                <div class="mb-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $review['rating']): ?>
                                            <i class="fas fa-star text-warning"></i>
                                        <?php else: ?>
                                            <i class="far fa-star text-warning"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                
                                <p class="card-text"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <!-- Form per nuova recensione -->
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Scrivi una recensione</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                            <div class="mb-3">
                                <label for="rating" class="form-label">Valutazione</label>
                                <div class="rating-stars">
                                    <div class="btn-group" role="group">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <input type="radio" class="btn-check" name="rating" id="rating-<?= $i ?>" value="<?= $i ?>" autocomplete="off" required>
                                            <label class="btn btn-outline-warning" for="rating-<?= $i ?>">
                                                <i class="fas fa-star"></i>
                                            </label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="comment" class="form-label">Commento</label>
                                <textarea class="form-control" id="comment" name="comment" rows="4" required></textarea>
                            </div>
                            
                            <button type="submit" name="add_review" class="btn btn-primary">Pubblica recensione</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>