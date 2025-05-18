<?php
// File di configurazione per la connessione al database
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Utente di default per XAMPP
define('DB_PASS', '');     // Password vuota di default per XAMPP
define('DB_NAME', 'audiowear'); // Utilizziamo il secondo database (audio_wear)

// Funzione per connessione al database
function connectDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Verifica connessione
    if ($conn->connect_error) {
        die("Connessione fallita: " . $conn->connect_error);
    }
    
    // Imposta charset per supportare caratteri speciali
    $conn->set_charset("utf8mb4");
    
    return $conn;
}