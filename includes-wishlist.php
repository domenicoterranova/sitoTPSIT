<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Funzione per aggiungere un prodotto alla wishlist
function addToWishlist($productId) {
    if (!isLoggedIn()) {
        return ["success" => false, "message" => "Devi accedere per aggiungere prodotti alla wishlist."];
    }
    
    $userId = $_SESSION['user_id'];
    $conn = connectDB();
    
    // Verifica che il prodotto esista
    $checkProduct = $conn->prepare("SELECT product_id FROM products WHERE product_id = ? AND is_active = 1");
    $checkProduct->bind_param("i", $productId);
    $checkProduct->execute();
    
    if ($checkProduct->get_result()->num_rows === 0) {
        $checkProduct->close();
        $conn->close();
        return ["success" => false, "message" => "Prodotto non disponibile."];
    }
    
    // Verifica se il prodotto è già nella wishlist
    $checkWishlist = $conn->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $checkWishlist->bind_param("ii", $userId, $productId);
    $checkWishlist->execute();
    
    if ($checkWishlist->get_result()->num_rows > 0) {
        $checkWishlist->close();
        $checkProduct->close();
        $conn->close();
        return ["success" => true, "message" => "Prodotto già presente nella wishlist."];
    }
    
    // Aggiungi il prodotto alla wishlist
    $addToWishlist = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $addToWishlist->bind_param("ii", $userId, $productId);
    $addToWishlist->execute();
    
    $checkWishlist->close();
    $checkProduct->close();
    $addToWishlist->close();
    $conn->close();
    
    return ["success" => true, "message" => "Prodotto aggiunto alla wishlist."];
}

// Funzione per rimuovere un prodotto dalla wishlist
function removeFromWishlist($wishlistId) {
    if (!isLoggedIn()) {
        return ["success" => false, "message" => "Devi accedere per modificare la wishlist."];
    }
    
    $userId = $_SESSION['user_id'];
    $conn = connectDB();
    
    // Verifica che l'elemento della wishlist appartenga all'utente
    $checkItem = $conn->prepare("SELECT wishlist_id FROM wishlist WHERE wishlist_id = ? AND user_id = ?");
    $checkItem->bind_param("ii", $wishlistId, $userId);
    $checkItem->execute();
    
    if ($checkItem->get_result()->num_rows === 0) {
        $checkItem->close();
        $conn->close();
        return ["success" => false, "message" => "Elemento non trovato nella wishlist."];
    }
    
    // Rimuovi l'elemento
    $removeItem = $conn->prepare("DELETE FROM wishlist WHERE wishlist_id = ?");
    $removeItem->bind_param("i", $wishlistId);
    $removeItem->execute();
    
    $checkItem->close();
    $removeItem->close();
    $conn->close();
    
    return ["success" => true, "message" => "Prodotto rimosso dalla wishlist."];
}

// Funzione per ottenere la wishlist dell'utente
function getUserWishlist() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $userId = $_SESSION['user_id'];
    $conn = connectDB();
    
    $stmt = $conn->prepare("
        SELECT w.wishlist_id, w.product_id, w.added_at, p.name, p.description, p.price, p.image_url, c.name AS category_name
        FROM wishlist w
        JOIN products p ON w.product_id = p.product_id
        JOIN categories c ON p.category_id = c.category_id
        WHERE w.user_id = ?
        ORDER BY w.added_at DESC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $wishlist = [];
    while ($item = $result->fetch_assoc()) {
        $wishlist[] = $item;
    }
    
    $stmt->close();
    $conn->close();
    
    return $wishlist;
}

// Funzione per verificare se un prodotto è nella wishlist dell'utente
function isInWishlist($productId) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $userId = $_SESSION['user_id'];
    $conn = connectDB();
    
    $stmt = $conn->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $isInWishlist = $result->num_rows > 0;
    
    $stmt->close();
    $conn->close();
    
    return $isInWishlist;
}