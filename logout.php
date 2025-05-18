<?php
session_start();
require_once 'includes/auth.php';

// Effettua il logout
$result = logoutUser();

// Reindirizza alla home page
header("Location: index.php");
exit();