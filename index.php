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
    
    case '/reparaties':
        require_once 'private/controllers/RepController.php';
        $controller = new RepController();
        $controller->index();
        break;
    
    case '/bak':
        require_once 'private/controllers/BakController.php';
        $controller = new BakController();
        $controller->index();
        break;
    
    case '/complete':
        require_once 'private/controllers/CompleteController.php';
        $controller = new CompleteController();
        $controller->index();
        break;

    default:
    echo "404 Page Not Found";
    break;

}