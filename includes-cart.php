<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Funzione per ottenere il carrello dell'utente corrente
function getCurrentCart() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $userId = $_SESSION['user_id'];
    $conn = connectDB();
    
    // Trova il carrello dell'utente
    $stmt = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Crea un nuovo carrello se non esiste
        $createCart = $conn->prepare("INSERT INTO cart (user_id) VALUES (?)");
        $createCart->bind_param("i", $userId);
        $createCart->execute();
        $cartId = $conn->insert_id;
        $createCart->close();
    } else {
        $cart = $result->fetch_assoc();
        $cartId = $cart['cart_id'];
    }
    
    // Ottieni gli elementi del carrello
    $itemsStmt = $conn->prepare("
        SELECT ci.*, p.name, p.price, p.image_url, (p.price * ci.quantity) as total_price
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.product_id
        WHERE ci.cart_id = ?
    ");
    $itemsStmt->bind_param("i", $cartId);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();
    
    $items = [];
    $totalQuantity = 0;
    $totalAmount = 0;
    
    while ($item = $itemsResult->fetch_assoc()) {
        $items[] = $item;
        $totalQuantity += $item['quantity'];
        $totalAmount += $item['total_price'];
    }
    
    $stmt->close();
    $itemsStmt->close();
    $conn->close();
    
    return [
        'cart_id' => $cartId,
        'items' => $items,
        'total_quantity' => $totalQuantity,
        'total_amount' => $totalAmount
    ];
}

// Funzione per aggiungere un prodotto al carrello
function addToCart($productId, $quantity = 1) {
    if (!isLoggedIn()) {
        return ["success" => false, "message" => "Devi accedere per aggiungere prodotti al carrello."];
    }
    
    $userId = $_SESSION['user_id'];
    $conn = connectDB();
    
    // Verifica che il prodotto esista e sia disponibile
    $checkProduct = $conn->prepare("SELECT product_id, stock_quantity FROM products WHERE product_id = ? AND is_active = 1");
    $checkProduct->bind_param("i", $productId);
    $checkProduct->execute();
    $productResult = $checkProduct->get_result();
    
    if ($productResult->num_rows === 0) {
        $checkProduct->close();
        $conn->close();
        return ["success" => false, "message" => "Prodotto non disponibile."];
    }
    
    $product = $productResult->fetch_assoc();
    if ($product['stock_quantity'] < $quantity) {
        $checkProduct->close();
        $conn->close();
        return ["success" => false, "message" => "Quantità richiesta non disponibile."];
    }
    
    // Trova o crea il carrello dell'utente
    $cartStmt = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
    $cartStmt->bind_param("i", $userId);
    $cartStmt->execute();
    $cartResult = $cartStmt->get_result();
    
    if ($cartResult->num_rows === 0) {
        // Crea un nuovo carrello
        $createCart = $conn->prepare("INSERT INTO cart (user_id) VALUES (?)");
        $createCart->bind_param("i", $userId);
        $createCart->execute();
        $cartId = $conn->insert_id;
        $createCart->close();
    } else {
        $cart = $cartResult->fetch_assoc();
        $cartId = $cart['cart_id'];
    }
    
    // Verifica se il prodotto è già nel carrello
    $checkItemStmt = $conn->prepare("SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $checkItemStmt->bind_param("ii", $cartId, $productId);
    $checkItemStmt->execute();
    $itemResult = $checkItemStmt->get_result();
    
    if ($itemResult->num_rows > 0) {
        // Aggiorna la quantità se il prodotto è già nel carrello
        $item = $itemResult->fetch_assoc();
        $newQuantity = $item['quantity'] + $quantity;
        
        $updateItem = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?");
        $updateItem->bind_param("ii", $newQuantity, $item['cart_item_id']);
        $updateItem->execute();
        $updateItem->close();
    } else {
        // Aggiungi nuovo elemento al carrello
        $addItem = $conn->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)");
        $addItem->bind_param("iii", $cartId, $productId, $quantity);
        $addItem->execute();
        $addItem->close();
    }
    
    $cartStmt->close();
    $checkItemStmt->close();
    $checkProduct->close();
    $conn->close();
    
    return ["success" => true, "message" => "Prodotto aggiunto al carrello."];
}

// Funzione per aggiornare la quantità di un prodotto nel carrello
function updateCartItemQuantity($cartItemId, $quantity) {
    if (!isLoggedIn()) {
        return ["success" => false, "message" => "Devi accedere per modificare il carrello."];
    }
    
    if ($quantity <= 0) {
        return removeFromCart($cartItemId);
    }
    
    $userId = $_SESSION['user_id'];
    $conn = connectDB();
    
    // Verifica che l'elemento del carrello appartenga all'utente
    $checkItem = $conn->prepare("
        SELECT ci.cart_item_id, p.stock_quantity 
        FROM cart_items ci
        JOIN cart c ON ci.cart_id = c.cart_id
        JOIN products p ON ci.product_id = p.product_id
        WHERE ci.cart_item_id = ? AND c.user_id = ?
    ");
    $checkItem->bind_param("ii", $cartItemId, $userId);
    $checkItem->execute();
    $itemResult = $checkItem->get_result();
    
    if ($itemResult->num_rows === 0) {
        $checkItem->close();
        $conn->close();
        return ["success" => false, "message" => "Elemento non trovato nel carrello."];
    }
    
    $item = $itemResult->fetch_assoc();
    if ($quantity > $item['stock_quantity']) {
        $checkItem->close();
        $conn->close();
        return ["success" => false, "message" => "Quantità richiesta non disponibile."];
    }
    
    // Aggiorna la quantità
    $updateItem = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?");
    $updateItem->bind_param("ii", $quantity, $cartItemId);
    $updateItem->execute();
    
    $checkItem->close();
    $updateItem->close();
    $conn->close();
    
    return ["success" => true, "message" => "Quantità aggiornata."];
}

// Funzione per rimuovere un prodotto dal carrello
function removeFromCart($cartItemId) {
    if (!isLoggedIn()) {
        return ["success" => false, "message" => "Devi accedere per modificare il carrello."];
    }
    
    $userId = $_SESSION['user_id'];
    $conn = connectDB();
    
    // Verifica che l'elemento del carrello appartenga all'utente
    $checkItem = $conn->prepare("
        SELECT ci.cart_item_id
        FROM cart_items ci
        JOIN cart c ON ci.cart_id = c.cart_id
        WHERE ci.cart_item_id = ? AND c.user_id = ?
    ");
    $checkItem->bind_param("ii", $cartItemId, $userId);
    $checkItem->execute();
    $itemResult = $checkItem->get_result();
    
    if ($itemResult->num_rows === 0) {
        $checkItem->close();
        $conn->close();
        return ["success" => false, "message" => "Elemento non trovato nel carrello."];
    }
    
    // Rimuovi l'elemento
    $removeItem = $conn->prepare("DELETE FROM cart_items WHERE cart_item_id = ?");
    $removeItem->bind_param("i", $cartItemId);
    $removeItem->execute();
    
    $checkItem->close();
    $removeItem->close();
    $conn->close();
    
    return ["success" => true, "message" => "Prodotto rimosso dal carrello."];
}

// Funzione per creare un ordine dal carrello
function createOrderFromCart($shippingAddress, $shippingCity, $shippingPostalCode, $shippingCountry, $paymentMethod) {
    if (!isLoggedIn()) {
        return ["success" => false, "message" => "Devi accedere per completare l'ordine."];
    }
    
    $userId = $_SESSION['user_id'];
    $conn = connectDB();
    
    // Ottieni il carrello dell'utente
    $cart = getCurrentCart();
    
    if ($cart === null || count($cart['items']) === 0) {
        $conn->close();
        return ["success" => false, "message" => "Il carrello è vuoto."];
    }
    
    // Inizia la transazione
    $conn->begin_transaction();
    
    try {
        // Crea un nuovo ordine
        $createOrder = $conn->prepare("
            INSERT INTO orders (
                user_id, status_id, total_amount, shipping_address, shipping_city, 
                shipping_postal_code, shipping_country, payment_method
            ) VALUES (?, 1, ?, ?, ?, ?, ?, ?)
        ");
        $createOrder->bind_param(
            "idssss", 
            $userId, 
            $cart['total_amount'], 
            $shippingAddress, 
            $shippingCity, 
            $shippingPostalCode, 
            $shippingCountry, 
            $paymentMethod
        );
        $createOrder->execute();
        $orderId = $conn->insert_id;
        
        // Aggiungi gli elementi dell'ordine
        foreach ($cart['items'] as $item) {
            $addItem = $conn->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price) 
                VALUES (?, ?, ?, ?)
            ");
            $addItem->bind_param("iiid", $orderId, $item['product_id'], $item['quantity'], $item['price']);
            $addItem->execute();
            
            // Aggiorna la quantità disponibile del prodotto
            $updateStock = $conn->prepare("
                UPDATE products 
                SET stock_quantity = stock_quantity - ? 
                WHERE product_id = ?
            ");
            $updateStock->bind_param("ii", $item['quantity'], $item['product_id']);
            $updateStock->execute();
            
            $addItem->close();
            $updateStock->close();
        }
        
        // Svuota il carrello
        $emptyCart = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        $emptyCart->bind_param("i", $cart['cart_id']);
        $emptyCart->execute();
        $emptyCart->close();
        
        // Conferma la transazione
        $conn->commit();
        
        $createOrder->close();
        $conn->close();
        
        return [
            "success" => true, 
            "message" => "Ordine completato con successo.", 
            "order_id" => $orderId
        ];
    } catch (Exception $e) {
        // Annulla la transazione in caso di errore
        $conn->rollback();
        $conn->close();
        return ["success" => false, "message" => "Errore durante la creazione dell'ordine: " . $e->getMessage()];
    }
}

// Funzione per ottenere gli ordini dell'utente
function getUserOrders() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $userId = $_SESSION['user_id'];
    $conn = connectDB();
    
    $stmt = $conn->prepare("
        SELECT o.*, os.name as status_name 
        FROM orders o
        JOIN order_status os ON o.status_id = os.status_id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    while ($order = $result->fetch_assoc()) {
        // Ottieni i dettagli dell'ordine
        $itemsStmt = $conn->prepare("
            SELECT oi.*, p.name, p.image_url
            FROM order_items oi
            JOIN products p ON oi.product_id = p.product_id
            WHERE oi.order_id = ?
        ");
        $itemsStmt->bind_param("i", $order['order_id']);
        $itemsStmt->execute();
        $itemsResult = $itemsStmt->get_result();
        
        $items = [];
        while ($item = $itemsResult->fetch_assoc()) {
            $items[] = $item;
        }
        
        $order['items'] = $items;
        $orders[] = $order;
        
        $itemsStmt->close();
    }
    
    $stmt->close();
    $conn->close();
    
    return $orders;
}

// Funzione per ottenere un singolo ordine
function getOrderById($orderId) {
    if (!isLoggedIn()) {
        return null;
    }
    
    $userId = $_SESSION['user_id'];
    $conn = connectDB();
    
    $stmt = $conn->prepare("
        SELECT o.*, os.name as status_name 
        FROM orders o
        JOIN order_status os ON o.status_id = os.status_id
        WHERE o.order_id = ? AND o.user_id = ?
    ");
    $stmt->bind_param("ii", $orderId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        return null;
    }
    
    $order = $result->fetch_assoc();
    
    // Ottieni i dettagli dell'ordine
    $itemsStmt = $conn->prepare("
        SELECT oi.*, p.name, p.image_url
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?
    ");
    $itemsStmt->bind_param("i", $orderId);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();
    
    $items = [];
    while ($item = $itemsResult->fetch_assoc()) {
        $items[] = $item;
    }
    
    $order['items'] = $items;
    
    $stmt->close();
    $itemsStmt->close();
    $conn->close();
    
    return $order;
}