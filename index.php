<?php 

$request = $_SERVER['REQUEST_URI'];
$basePath = '';
$request = str_replace($basePath, '', $request);
$request = strtok($request, '?');

switch ($request) {
    case '/':
    case '/home':
        require_once 'private/controllers/HomeController.php';
        $controller = new HomeController();
        $controller->index();
        break;

    case '/login':
        require_once 'private/controllers/LoginController.php';
        $controller = new LoginController();
        $controller->index();
        break;

    default:
    echo "404 Page Not Found";
    break;

}