<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

// Reindirizza se già loggato
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$errors = [];
$username = '';

// Controllo se c'è un messaggio di registrazione avvenuta con successo
$registrationSuccess = isset($_SESSION['registration_success']) && $_SESSION['registration_success'] === true;
if ($registrationSuccess) {
    // Rimuovi il flag dalla sessione
    unset($_SESSION['registration_success']);
}

// Gestione del form di login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validazione
    if (empty($username)) {
        $errors[] = "Username o email obbligatori.";
    }
    
    if (empty($password)) {
        $errors[] = "Password obbligatoria.";
    }
    
    // Se non ci sono errori, procedi con il login
    if (empty($errors)) {
        $result = loginUser($username, $password);
        
        if ($result['success']) {
            // Reindirizza alla home page o alla pagina richiesta in precedenza
            $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
            unset($_SESSION['redirect_after_login']);
            
            header("Location: $redirect");
            exit();
        } else {
            $errors[] = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accedi - AudioWear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Accedi</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($registrationSuccess): ?>
                            <div class="alert alert-success">
                                Registrazione completata con successo! Ora puoi accedere con le tue credenziali.
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" novalidate>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username o Email</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                                <label class="form-check-label" for="remember_me">Ricordami</label>
                                <a href="forgot_password.php" class="float-end">Password dimenticata?</a>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Accedi</button>
                            </div>
                        </form>
                        
                        <hr>
                        <div class="text-center">
                            Non hai un account? <a href="register.php">Registrati</a>
                        </div>
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