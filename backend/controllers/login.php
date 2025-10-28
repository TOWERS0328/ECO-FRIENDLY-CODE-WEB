<?php
header("Content-Type: application/json");
include_once "../config/database.php";
include_once "../models/Estudiante.php";

$database = new Database();
$db = $database->connect();

$data = json_decode(file_get_contents("php://input"));
$estudiante = new Estudiante($db);

if (empty($data->email) || empty($data->password)) {
    echo json_encode(["status" => "error", "message" => "Campos incompletos."]);
    exit;
}

$estudiante->email = $data->email;
$stmt = $estudiante->login();

if ($stmt->rowCount() == 0) {
    echo json_encode(["status" => "error", "message" => "Correo no encontrado."]);
    exit;
}

$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (password_verify($data->password, $row["password"])) {
    echo json_encode(["status" => "success", "message" => "Inicio de sesión exitoso."]);
} else {
    echo json_encode(["status" => "error", "message" => "Contraseña incorrecta."]);
}
?>
