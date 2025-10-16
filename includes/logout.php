<?php
require_once __DIR__ . '/../includes/auth.php';

// Logout user
$auth->logout();

// Redirect to home page
header('Location: ../index.php');
exit;
?>