<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET");
header("Content-Type: application/json");

$routes = []; // array global que contendrá todas las rutas

// Cargar rutas por módulo
require_once __DIR__ . "/routes/estudiante.php";
require_once __DIR__ . "/routes/premio.php";
require_once __DIR__ . "/routes/empresa.php";
require_once __DIR__ . "/routes/residuo.php";
require_once __DIR__ . "/routes/actor.php";
require_once __DIR__ . "/routes/acopio.php";

$route = $_GET['route'] ?? null;

if(isset($routes[$route])) {
    [$controllerName, $method] = $routes[$route];
    require_once __DIR__ . "/app/controllers/$controllerName.php";
    $controller = new $controllerName();
    $controller->$method();
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Ruta no encontrada"
    ]);
}
