<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET");
header("Content-Type: application/json");

$route = $_GET['route'] ?? null;

switch ($route) {
    case "estudiante.registrar":
        require_once __DIR__ . "/app/controllers/EstudianteController.php";
        $controller = new EstudianteController();
        $controller->registrar();
        break;

    case "estudiante.listar":
        require_once __DIR__ . "/app/controllers/EstudianteController.php";
        $controller = new EstudianteController();
        $controller->listar();
        break;

    case "estudiante.actualizar":
        require_once __DIR__ . "/app/controllers/EstudianteController.php";
        $controller = new EstudianteController();
        $controller->actualizar();
        break;

    case "estudiante.restablecer":
        require_once __DIR__ . "/app/controllers/EstudianteController.php";
        $controller = new EstudianteController();
        $controller->restablecer();
        break;


    case "actor.login":
        require_once __DIR__ . "/app/controllers/LoginController.php";
        $controller = new LoginController();
        $controller->login();
        break;

    default:
        echo json_encode([
            "status" => "error",
            "message" => "Ruta no encontrada"
        ]);
        break;
}
