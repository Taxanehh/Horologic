<?php
// private/bootstrap.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current path
$request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Only load header and auth check for non-login pages
if ($request_path !== '/login') {
    require_once __DIR__ . '/controllers/AuthController.php';
    AuthController::checkAuth();
    require_once __DIR__ . '/views/layout/header.php';
}