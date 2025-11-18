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

    // PREMIOS
    case "premio.listar":
        require_once __DIR__ . "/app/controllers/PremioController.php";
        $controller = new PremioController();
        $controller->listar();
        break;

    case "premio.crear":
        require_once __DIR__ . "/app/controllers/PremioController.php";
        $controller = new PremioController();
        $controller->crear();
        break;

    case "premio.actualizar":
        require_once __DIR__ . "/app/controllers/PremioController.php";
        $controller = new PremioController();
        $controller->actualizar();
        break;

    case "empresa.listar":
        require_once __DIR__ . "/app/controllers/EmpresaController.php";
        $controller = new EmpresaController();
        $controller->listar();
        break;

    case "empresa.listarActivas":
        require_once __DIR__ . "/app/controllers/EmpresaController.php";
        $controller = new EmpresaController();
        $controller->listarActivas();
        break;

    case "empresa.registrar":
        require_once __DIR__ . "/app/controllers/EmpresaController.php";
        $controller = new EmpresaController();
        $controller->registrar();
        break;

    case "empresa.actualizar":
        require_once __DIR__ . "/app/controllers/EmpresaController.php";
        $controller = new EmpresaController();
        $controller->actualizar();
        break;

    case "empresa.obtener":
        require_once __DIR__ . "/app/controllers/EmpresaController.php";
        $controller = new EmpresaController();
        $controller->obtener();
        break;

    // RESIDUOS
case "residuo.listar":
    require_once __DIR__ . "/app/controllers/ResiduoController.php";
    $controller = new ResiduoController();
    $controller->listar();
    break;

case "residuo.crear":
    require_once __DIR__ . "/app/controllers/ResiduoController.php";
    $controller = new ResiduoController();
    $controller->crear();
    break;

case "residuo.actualizar":
    require_once __DIR__ . "/app/controllers/ResiduoController.php";
    $controller = new ResiduoController();
    $controller->actualizar();
    break;

case "residuo.obtener":
    require_once __DIR__ . "/app/controllers/ResiduoController.php";
    $controller = new ResiduoController();
    $controller->obtener();
    break;


    default:
        echo json_encode([
            "status" => "error",
            "message" => "Ruta no encontrada"
        ]);
        break;
}
