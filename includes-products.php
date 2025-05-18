<?php
require_once 'config/database.php';

// Funzione per ottenere tutti i prodotti
function getAllProducts($limit = null, $category = null, $feature = null) {
    $conn = connectDB();
    
    // Query base con i nomi di tabella corretti
    $sql = "SELECT p.*, c.nome AS category_name FROM prodotti p 
            JOIN categorie c ON p.categoria_id = c.id 
            WHERE 1=1";
    
    // Aggiungi condizioni in base ai parametri
    $params = [];
    $types = "";
    
    // Filtra per categoria
    if ($category !== null) {
        $sql .= " AND p.categoria_id = ?";
        $params[] = $category;
        $types .= "i";
    }
    
    // Filtra per caratteristiche speciali
    if ($feature !== null) {
        // Mappiamo i valori delle feature ai campi nella tabella prodotti
        switch($feature) {
            case 1: // Wireless
                $sql .= " AND p.wireless = 1";
                break;
            case 2: // Noise Cancelling
                $sql .= " AND p.noise_cancelling = 1";
                break;
            case 3: // Microfono
                $sql .= " AND p.microfono = 1";
                break;
            case 4: // Resistente all'acqua
                $sql .= " AND p.resistente_acqua = 1";
                break;
        }
    }
    
    // Ordina per data di inserimento
    $sql .= " ORDER BY p.data_inserimento DESC";
    
    // Limita il numero di risultati
    if ($limit !== null) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
        $types .= "i";
    }
    
    // Esegui la query
    if (empty($params)) {
        // Nessun parametro, usa query diretta
        $result = $conn->query($sql);
        if (!$result) {
            echo "Errore nella query: " . $conn->error;
            $conn->close();
            return [];
        }
    } else {
        // Ci sono parametri, usa prepared statement
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo "Errore nella preparazione della query: " . $conn->error;
            $conn->close();
            return [];
        }
        
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    }
    
    // Elabora i risultati
    $products = [];
    while ($row = $result->fetch_assoc()) {
        // Aggiungi array di caratteristiche vuoto per compatibilità
        $row['features'] = [];
        
        // Aggiungi caratteristiche in base ai campi booleani
        if ($row['wireless']) {
            $row['features'][] = [
                'id' => 1,
                'nome' => 'Wireless',
                'descrizione' => 'Connessione senza fili'
            ];
        }
        
        if ($row['noise_cancelling']) {
            $row['features'][] = [
                'id' => 2,
                'nome' => 'Noise Cancelling',
                'descrizione' => 'Cancellazione attiva del rumore'
            ];
        }
        
        if ($row['microfono']) {
            $row['features'][] = [
                'id' => 3,
                'nome' => 'Microfono',
                'descrizione' => 'Con microfono integrato'
            ];
        }
        
        if ($row['resistente_acqua']) {
            $row['features'][] = [
                'id' => 4,
                'nome' => 'Resistente all\'acqua',
                'descrizione' => 'Impermeabile o resistente agli schizzi'
            ];
        }
        
        $products[] = $row;
    }
    
    // Chiudi le risorse
    if (isset($stmt)) {
        $stmt->close();
    } else if (isset($result)) {
        $result->close();
    }
    
    $conn->close();
    
    return $products;
}

// Funzione per ottenere un singolo prodotto
function getProductById($productId) {
    $conn = connectDB();
    
    $sql = "SELECT p.*, c.nome AS category_name 
            FROM prodotti p
            JOIN categorie c ON p.categoria_id = c.id
            WHERE p.id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Errore nella preparazione della query: " . $conn->error;
        $conn->close();
        return null;
    }
    
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        return null;
    }
    
    $product = $result->fetch_assoc();
    
    // Non cerchiamo caratteristiche poiché non abbiamo quella tabella
    $product['features'] = [];
    
    // Ottieni le recensioni del prodotto
    $reviewsStmt = $conn->prepare("
        SELECT r.*, u.nome AS username 
        FROM recensioni r
        JOIN utenti u ON r.utente_id = u.id
        WHERE r.prodotto_id = ?
        ORDER BY r.data_recensione DESC
    ");
    
    if (!$reviewsStmt) {
        echo "Errore nella preparazione della query recensioni: " . $conn->error;
        $stmt->close();
        $conn->close();
        return $product;
    }
    
    $reviewsStmt->bind_param("i", $productId);
    $reviewsStmt->execute();
    $reviewsResult = $reviewsStmt->get_result();
    
    $reviews = [];
    while ($review = $reviewsResult->fetch_assoc()) {
        $reviews[] = $review;
    }
    $product['reviews'] = $reviews;
    
    $stmt->close();
    $reviewsStmt->close();
    $conn->close();
    
    return $product;
}

// Funzione per ottenere tutte le categorie
function getAllCategories() {
    $conn = connectDB();
    
    // Usa il nome della tabella in italiano
    $result = $conn->query("SELECT * FROM categorie");
    
    if (!$result) {
        // Se la query fallisce, stampa l'errore e restituisci un array vuoto
        echo "Errore nella query: " . $conn->error;
        $conn->close();
        return [];
    }
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    
    $result->close();
    $conn->close();
    
    return $categories;
}

// Funzione per ottenere tutte le caratteristiche (funzione fittizzia per compatibilità)
function getAllFeatures() {
    // Ritorna caratteristiche fittizie basate sui campi disponibili in prodotti
    return [
        ['id' => 1, 'nome' => 'Wireless', 'descrizione' => 'Connessione senza fili'],
        ['id' => 2, 'nome' => 'Noise Cancelling', 'descrizione' => 'Cancellazione attiva del rumore'],
        ['id' => 3, 'nome' => 'Microfono', 'descrizione' => 'Con microfono integrato'],
        ['id' => 4, 'nome' => 'Resistente all\'acqua', 'descrizione' => 'Impermeabile o resistente agli schizzi']
    ];
}

// Funzione per cercare prodotti
function searchProducts($searchTerm) {
    $conn = connectDB();
    
    $searchTerm = "%$searchTerm%";
    
    $stmt = $conn->prepare("
        SELECT p.*, c.name AS category_name 
        FROM products p
        JOIN categories c ON p.category_id = c.category_id
        WHERE p.is_active = 1 AND (
            p.name LIKE ? OR
            p.description LIKE ? OR
            c.name LIKE ?
        )
        ORDER BY p.created_at DESC
    ");
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        // Ottieni le caratteristiche del prodotto
        $featuresStmt = $conn->prepare("
            SELECT f.feature_id, f.name, f.description 
            FROM features f
            JOIN product_features pf ON f.feature_id = pf.feature_id
            WHERE pf.product_id = ?
        ");
        $featuresStmt->bind_param("i", $row['product_id']);
        $featuresStmt->execute();
        $featuresResult = $featuresStmt->get_result();
        
        $features = [];
        while ($feature = $featuresResult->fetch_assoc()) {
            $features[] = $feature;
        }
        $row['features'] = $features;
        $featuresStmt->close();
        
        $products[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return $products;
}