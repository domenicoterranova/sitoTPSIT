<?php
// Verifica se la sessione è già attiva
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';

// Funzione per registrare un nuovo utente
function registerUser($username, $password, $email, $firstName, $lastName, $address = null, $city = null, $postalCode = null, $country = null, $phone = null) {
    $conn = connectDB();
    
    // Verifica se l'utente o l'email esistono già
    $checkUser = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
    $checkUser->bind_param("ss", $username, $email);
    $checkUser->execute();
    $result = $checkUser->get_result();
    
    if ($result->num_rows > 0) {
        $checkUser->close();
        $conn->close();
        return ["success" => false, "message" => "Username o email già in uso."];
    }
    
    // Hash della password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Inserimento nuovo utente
    $stmt = $conn->prepare("INSERT INTO users (username, password, email, first_name, last_name, address, city, postal_code, country, phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssss", $username, $hashedPassword, $email, $firstName, $lastName, $address, $city, $postalCode, $country, $phone);
    
    if ($stmt->execute()) {
        // Crea un carrello per il nuovo utente
        $userId = $conn->insert_id;
        $createCart = $conn->prepare("INSERT INTO cart (user_id) VALUES (?)");
        $createCart->bind_param("i", $userId);
        $createCart->execute();
        $createCart->close();
        
        $stmt->close();
        $conn->close();
        return ["success" => true, "message" => "Registrazione completata con successo."];
    } else {
        $stmt->close();
        $conn->close();
        return ["success" => false, "message" => "Errore durante la registrazione: " . $conn->error];
    }
}

// Funzione per effettuare il login
function loginUser($username, $password) {
    $conn = connectDB();
    
    // Trova l'utente con username o email
    $stmt = $conn->prepare("SELECT user_id, username, password FROM users WHERE (username = ? OR email = ?) AND is_active = 1");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verifica password
        if (password_verify($password, $user['password'])) {
            // Aggiorna data ultimo accesso
            $updateLogin = $conn->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?");
            $updateLogin->bind_param("i", $user['user_id']);
            $updateLogin->execute();
            $updateLogin->close();
            
            // Inizializza sessione
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['logged_in'] = true;
            
            $stmt->close();
            $conn->close();
            return ["success" => true, "message" => "Login effettuato con successo."];
        }
    }
    
    $stmt->close();
    $conn->close();
    return ["success" => false, "message" => "Username/email o password non validi."];
}

// Funzione per verificare se l'utente è loggato
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Funzione per effettuare il logout
function logoutUser() {
    // Elimina tutte le variabili di sessione
    $_SESSION = array();
    
    // Elimina il cookie di sessione
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Distruggi la sessione
    session_destroy();
    
    return ["success" => true, "message" => "Logout effettuato con successo."];
}

// Funzione per ottenere i dati dell'utente corrente
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $userId = $_SESSION['user_id'];
    $conn = connectDB();
    
    $stmt = $conn->prepare("SELECT user_id, username, email, first_name, last_name, address, city, postal_code, country, phone FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $user;
    }
    
    $stmt->close();
    $conn->close();
    return null;
}