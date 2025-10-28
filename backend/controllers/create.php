<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once "../config/database.php";

$database = new Database();
$conn = $database->connect();

// Obtener datos del cuerpo de la solicitud
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->nombre) && !empty($data->descripcion) && isset($data->puntos_otorgados)) {
    $query = "INSERT INTO residuos (nombre, descripcion, puntos_otorgados)
              VALUES (:nombre, :descripcion, :puntos_otorgados)";
    $stmt = $conn->prepare($query);
    
    $stmt->bindParam(":nombre", $data->nombre);
    $stmt->bindParam(":descripcion", $data->descripcion);
    $stmt->bindParam(":puntos_otorgados", $data->puntos_otorgados);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Residuo registrado correctamente"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al registrar el residuo"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
}
?>
