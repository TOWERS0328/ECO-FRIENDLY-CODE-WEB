<?php
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

    default:
        echo json_encode([
            "status" => "error",
            "message" => "Ruta no encontrada"
        ]);
        break;
}


