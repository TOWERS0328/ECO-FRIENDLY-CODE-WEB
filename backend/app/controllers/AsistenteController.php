<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Asistente.php';
require_once __DIR__ . '/../models/Acopio.php';
require_once __DIR__ . '/../models/Canje.php';


class AsistenteController
{
    public function registrar()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) {
            echo json_encode(["status"=>"error","message"=>"No se recibieron datos"]);
            return;
        }

        $required = ["cedula","nombre","genero","correo","contrasena","confirmar","area"];
        foreach($required as $f){
            if(empty($data[$f])){
                echo json_encode(["status"=>"error","field"=>$f,"message"=>"El campo $f es obligatorio"]);
                return;
            }
        }

        if($data['contrasena'] !== $data['confirmar']){
            echo json_encode(["status"=>"error","field"=>"contrasena","message"=>"Las contraseñas no coinciden"]);
            return;
        }

        if(!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)){
            echo json_encode(["status"=>"error","field"=>"correo","message"=>"Correo no válido"]);
            return;
        }

        $usuarioModel = new Usuario();
        $asistenteModel = new Asistente();

        if($usuarioModel->existeCorreo($data['correo'])){
            echo json_encode(["status"=>"error","field"=>"correo","message"=>"El correo ya está registrado"]);
            return;
        }

        if($asistenteModel->existeCedula($data['cedula'])){
            echo json_encode(["status"=>"error","field"=>"cedula","message"=>"La cédula ya está registrada"]);
            return;
        }

        $passwordHash = password_hash($data['contrasena'], PASSWORD_BCRYPT);

        $id_usuario = $usuarioModel->crearUsuario($data['correo'],$passwordHash,'asistente');
        if(!$id_usuario){
            echo json_encode(["status"=>"error","message"=>"Error creando usuario"]);
            return;
        }

        $ok = $asistenteModel->crearAsistente(
            $id_usuario,
            $data['cedula'],
            $data['nombre'],
            $data['apellido'],  
            $data['genero'],
            $data['area']
        );

        if(!$ok){
            echo json_encode(["status"=>"error","message"=>"Error creando asistente"]);
            return;
        }

        echo json_encode(["status"=>"success","message"=>"Asistente registrado correctamente"]);
    }

    public function listar()
    {
        header("Content-Type: application/json");
        $asistenteModel = new Asistente();
        echo json_encode($asistenteModel->getAllAsistentes());
    }

    public function actualizar()
    {
        header("Content-Type: application/json");
        $data = json_decode(file_get_contents("php://input"), true);

        if(!$data || !isset($data['id_asistente'])){
            echo json_encode(["status"=>"error","message"=>"ID requerido"]);
            return;
        }

        $required = ["id_asistente","cedula","nombre","apellido","genero","area"];
        foreach($required as $f){
            if(!isset($data[$f]) || trim($data[$f])===""){
                echo json_encode(["status"=>"error","field"=>$f,"message"=>"El campo $f es obligatorio"]);
                return;
            }
        }

        $asistenteModel = new Asistente();
        $usuarioModel = new Usuario();

        $asistente = $asistenteModel->getById($data['id_asistente']);
        if(!$asistente){
            echo json_encode(["status"=>"error","message"=>"Asistente no encontrado"]);
            return;
        }

        $id_usuario = $asistente['id_usuarioA'];

        if($asistenteModel->correoExisteEnOtro($data['correo'] ?? '', $id_usuario)){
            echo json_encode(["status"=>"error","field"=>"correo","message"=>"El correo ya está registrado por otro usuario"]);
            return;
        }

        $asistenteModel->actualizarAsistente(
            $data['id_asistente'],
            $data['cedula'],
            $data['nombre'],
            $data['apellido'],
            $data['genero'],
            $data['area']
        );

        if(isset($data['correo'])){
            $asistenteModel->actualizarCorreoUsuario($id_usuario,$data['correo']);
        }

        echo json_encode(["status"=>"success","message"=>"Asistente actualizado correctamente"]);
    }

    public function restablecer()
    {
        header("Content-Type: application/json");
        $data = json_decode(file_get_contents("php://input"), true);

        if(!$data || !isset($data['id_asistente'])){
            echo json_encode(["status"=>"error","message"=>"ID requerido"]);
            return;
        }

        $asistenteModel = new Asistente();
        $asistente = $asistenteModel->getById($data['id_asistente']);
        if(!$asistente){
            echo json_encode(["status"=>"error","message"=>"Asistente no encontrado"]);
            return;
        }

        $id_usuario = $asistente['id_usuarioA'];
        $tempPass = "ECO-" . random_int(1000,9999);
        $hash = password_hash($tempPass,PASSWORD_BCRYPT);

        if($asistenteModel->actualizarPasswordUsuario($id_usuario,$hash)){
            echo json_encode(["status"=>"success","message"=>"Contraseña restablecida","temp_password"=>$tempPass]);
        } else {
            echo json_encode(["status"=>"error","message"=>"No se pudo restablecer"]);
        }
    }

    public function obtenerPorId()
    {
        header("Content-Type: application/json");
        if(!isset($_GET['id_asistente'])){
            echo json_encode(["status"=>"error","message"=>"ID requerido"]);
            return;
        }
        $model = new Asistente();
        $asistente = $model->getById($_GET['id_asistente']);
        if(!$asistente){
            echo json_encode(["status"=>"error","message"=>"Asistente no encontrado"]);
            return;
        }
        echo json_encode(["status"=>"success","data"=>$asistente]);
    }

        public function actualizarPerfilAsistente()
{
    header("Content-Type: application/json");

    if (!isset($_POST['id_asistente'])) {
        echo json_encode(["status" => "error", "message" => "ID requerido"]);
        return;
    }

    $asisModel = new Asistente();
    $usuarioModel = new Usuario();

    $asis = $asisModel->getById($_POST['id_asistente']);
    if (!$asis) {
        echo json_encode(["status" => "error", "message" => "Asistente no encontrado"]);
        return;
    }

    $id_usuario = $asis['id_usuarioA'];

    // Validar cédula en otro asistente
    if ($asisModel->existeCedula($_POST['cedula']) && $_POST['cedula'] !== $asis['cedula']) {
        echo json_encode(["status" => "error", "field" => "cedula", "message" => "La cédula ya pertenece a otro asistente"]);
        return;
    }

    // Validar correo
    if (isset($_POST['correo']) && $_POST['correo'] !== "") {
        if (!filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["status" => "error", "field" => "correo", "message" => "Correo no válido"]);
            return;
        }

        if ($asisModel->correoExisteEnOtro($_POST['correo'], $id_usuario)) {
            echo json_encode(["status" => "error", "field" => "correo", "message" => "El correo ya está registrado por otro usuario"]);
            return;
        }
    }

    // Subir foto
    $fotoPath = null;
    if (!empty($_FILES["foto"]["name"])) {
        $uploadDir = __DIR__ . "/../../uploads/asistentes/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext  = pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION);
        $name = time() . "_" . bin2hex(random_bytes(6)) . "." . $ext;
        $dest = $uploadDir . $name;

        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $dest)) {
            $fotoPath = "uploads/asistentes/" . $name;
        }
    }

    // Actualizar datos del asistente
    $asisModel->actualizarAsistente(
        $_POST['id_asistente'],
        $_POST['cedula'],
        $_POST['nombre'],
        $_POST['apellido'],
        $_POST['genero'],
        $_POST['area']
    );

    // Actualizar correo si viene
    if (!empty($_POST["correo"])) {
        $asisModel->actualizarCorreoUsuario($id_usuario, $_POST["correo"]);
    }

    // Actualizar foto
    if ($fotoPath) {
        $asisModel->actualizarFotoPerfil($_POST['id_asistente'], $fotoPath);
    }

    echo json_encode(["status" => "success", "message" => "Perfil de asistente actualizado correctamente"]);
}


public function obtenerPerfil()
{
    header("Content-Type: application/json");

    if (!isset($_GET['id_asistente'])) {
        echo json_encode(["status" => "error","message"=>"ID requerido"]);
        return;
    }

    $id_asistente = $_GET['id_asistente'];
    $model = new Asistente();

    $asistente = $model->getByIdAll($id_asistente);

    if (!$asistente) {
        echo json_encode(["status"=>"error","message"=>"Asistente no encontrado"]);
        return;
    }

    // ⚠️ Fallback de foto por defecto
    if (empty($asistente['foto_perfil'])) {
        $asistente['foto_perfil'] = "uploads/asistentes/default.png";
    }

    echo json_encode([
        "status" => "success",
        "data" => $asistente
    ]);
}

public function resumenDashboard() {
    header("Content-Type: application/json");

    try {
        $acopio = new Acopio();
        $canje = new Canje();

        $data = [
            'residuos_pendientes' => $acopio->contarPorEstado('pendiente'),
            'residuos_validados'  => $acopio->contarPorEstado('validado'),
            'canjes_pendientes'   => $canje->contarPorEstado('pendiente'),
            'canjes_entregados'   => $canje->contarPorEstado('entregado')
        ];

        echo json_encode(['status' => 'success', 'data' => $data]);

    } catch (Exception $e) {
        // Esto te ayudará a ver el error real
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}




}
?>
