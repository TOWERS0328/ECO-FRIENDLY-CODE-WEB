<?php
header("Content-Type: application/json");
include_once __DIR__ . "/config/database.php";

$database = new Database();
$conn = $database->connect();

if ($conn) {
    echo json_encode(["status" => "success", "message" => "Conexión exitosa a la base de datos"]);
} else {
    echo json_encode(["status" => "error", "message" => "No se pudo conectar"]);
}
?>
