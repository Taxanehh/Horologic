<?php
// private/bootstrap.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current path
$request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Define paths that do not require authentication (add more if needed)
$publicPaths = ['/login', '/accept_quote', '/thank-you'];

// Only load header and auth check if the current path is not public
if (!in_array($request_path, $publicPaths)) {
    require_once __DIR__ . '/controllers/AuthController.php';
    AuthController::checkAuth();
    require_once __DIR__ . '/views/layout/header.php';
}