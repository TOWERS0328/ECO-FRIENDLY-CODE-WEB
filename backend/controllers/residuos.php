<?php
header("Content-Type: application/json");
include_once(__DIR__ . "/../config/database.php");
include_once(__DIR__ . "/../models/Residuo.php");

$database = new Database();
$db = $database->connect();
$residuo = new Residuo($db);

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "POST") {
    $data = json_decode(file_get_contents("php://input"));

    if (empty($data->nombre) || empty($data->descripcion) || !isset($data->puntos)) {
        echo json_encode(["status" => "error", "message" => "Faltan datos"]);
        exit;
    }

    $residuo->nombre = $data->nombre;
    $residuo->descripcion = $data->descripcion;
    $residuo->puntos = $data->puntos;
    $residuo->tipo = isset($data->tipo) ? $data->tipo : null;

    if ($residuo->crear()) {
        echo json_encode(["status" => "success", "message" => "Residuo registrado correctamente"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al registrar residuo"]);
    }
}

if ($method === "GET") {
    $stmt = $residuo->obtenerTodos();
    $residuos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($residuos);
}
?>
