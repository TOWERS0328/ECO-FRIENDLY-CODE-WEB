<?php
header("Content-Type: application/json");

// 👇 Así garantizamos que PHP encuentre los archivos correctamente
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/Estudiante.php";

$database = new Database();
$db = $database->connect();

$data = json_decode(file_get_contents("php://input"));
$estudiante = new Estudiante($db);

if (
    empty($data->id) || empty($data->name) || empty($data->lastname) ||
    empty($data->email) || empty($data->password)
) {
    echo json_encode(["status" => "error", "message" => "Faltan datos obligatorios."]);
    exit;
}

$estudiante->id_estudiantil = $data->id;
$estudiante->nombre = $data->name;
$estudiante->apellido = $data->lastname;
$estudiante->genero = $data->gender ?? null;
$estudiante->carrera = $data->career ?? null;
$estudiante->email = $data->email;
$estudiante->password = password_hash($data->password, PASSWORD_BCRYPT);

$check = $estudiante->verificarEmail();
if ($check->rowCount() > 0) {
    echo json_encode(["status" => "error", "message" => "El correo ya está registrado."]);
    exit;
}

if ($estudiante->registrar()) {
    echo json_encode(["status" => "success", "message" => "Registro exitoso."]);
} else {
    echo json_encode(["status" => "error", "message" => "No se pudo registrar el estudiante."]);
}
?>
