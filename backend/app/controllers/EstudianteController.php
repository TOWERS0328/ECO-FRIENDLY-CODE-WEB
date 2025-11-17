<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Estudiante.php';

class EstudianteController
{

    public function registrar()
    {

        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) {
            echo json_encode(["status" => "error", "message" => "No se recibieron datos JSON"]);
            return;
        }

        $required = ["cedula", "nombre", "apellido", "genero", "correo", "contrasena", "confirmar", "carrera"];
        foreach ($required as $f) {
            if (empty($data[$f])) {
                echo json_encode(["status" => "error", "field" => $f, "message" => "El campo $f es obligatorio"]);
                return;
            }
        }

        if ($data['contrasena'] !== $data['confirmar']) {
            echo json_encode(["status" => "error", "field" => "contrasena", "message" => "Las contraseñas no coinciden"]);
            return;
        }

        if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["status" => "error", "field" => "correo", "message" => "Correo no válido"]);
            return;
        }

        $usuarioModel = new Usuario();
        $estModel     = new Estudiante();

        if ($usuarioModel->existeCorreo($data['correo'])) {
            echo json_encode(["status" => "error", "field" => "correo", "message" => "El correo ya está registrado"]);
            return;
        }

        if ($estModel->existeCedula($data['cedula'])) {
            echo json_encode(["status" => "error", "field" => "cedula", "message" => "La cédula ya está registrada"]);
            return;
        }

        try {
            $passwordHash = password_hash($data['contrasena'], PASSWORD_BCRYPT);

            // usar modelo usuario
            $id_usuario = $usuarioModel->crearUsuario(
                $data['correo'],
                $passwordHash,
                'estudiante'
            );

            if (!$id_usuario) {
                echo json_encode(["status" => "error", "message" => "Error creando usuario"]);
                return;
            }

            // usar modelo estudiante
            $ok = $estModel->crearPerfilEstudiante(
                $id_usuario,
                $data['nombre'],
                $data['apellido'],
                $data['genero'],
                $data['cedula'],
                $data['carrera']
            );

            if (!$ok) {
                echo json_encode(["status" => "error", "message" => "Error creando perfil"]);
                return;
            }

            echo json_encode(["status" => "success", "message" => "Estudiante registrado correctamente"]);
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Error al registrar"]);
        }
    }

    public function listar()
    {
        header("Content-Type: application/json");

        $estModel = new Estudiante();
        $stmt = $estModel->getAllEstudiantes();

        echo json_encode($stmt);
    }

 public function actualizar() {
    header("Content-Type: application/json");
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        echo json_encode(["status"=>"error","message"=>"No se recibieron datos"]);
        return;
    }

    // Validaciones
    $required = ["id_estudiante","nombre","apellido","genero","cedula","carrera"];
    foreach ($required as $f) {
        if (!isset($data[$f]) || $data[$f] === "") {
            echo json_encode(["status"=>"error","field"=>$f,"message"=>"El campo $f es obligatorio"]);
            return;
        }
    }

    $estModel = new Estudiante();
    $usuarioModel = new Usuario();

    $est = $estModel->getByIdEstudiante($data['id_estudiante']);

    if (!$est) {
        echo json_encode(["status"=>"error","message"=>"Estudiante no encontrado"]);
        return;
    }

    $id_usuario = $est['id_usuarioE'];

    // 🔴 Validar cédula en otro estudiante
    if ($estModel->cedulaExisteEnOtro($data['cedula'], $data['id_estudiante'])) {
        echo json_encode(["status"=>"error","field"=>"cedula","message"=>"La cédula ya está registrada en otro estudiante"]);
        return;
    }

    // 🟡 Validar correo si se envía
    if (isset($data['correo']) && $data['correo'] !== "") {
        if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["status"=>"error","field"=>"correo","message"=>"Correo no válido"]);
            return;
        }

        if ($estModel->correoExisteEnOtro($data['correo'], $id_usuario)) {
            echo json_encode(["status"=>"error","field"=>"correo","message"=>"El correo ya está registrado en otro usuario"]);
            return;
        }
    }

    // 🟢 Guardar update
    try {
        $ok = $estModel->actualizarPerfilEstudiante(
            $data['id_estudiante'],
            $data['nombre'],
            $data['apellido'],
            $data['genero'],
            $data['cedula'],
            $data['carrera']
        );

        if (isset($data['correo']) && $data['correo'] !== "") {
            $estModel->actualizarCorreoUsuario($id_usuario, $data['correo']);
        }

        echo json_encode(["status"=>"success","message"=>"Estudiante actualizado correctamente"]);
    } catch (Exception $e) {
        echo json_encode(["status"=>"error","message"=>"Error interno"]);
    }
}


    // restablecer contraseña: genera contraseña temporal y la guarda hasheada en tb_usuarios
    public function restablecer() {
        header("Content-Type: application/json");
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data || !isset($data['id_estudiante'])) {
            echo json_encode(["status"=>"error","message"=>"id_estudiante es requerido"]);
            return;
        }

        $estModel = new Estudiante();
        $est = $estModel->getByIdEstudiante($data['id_estudiante']);
        if (!$est) {
            echo json_encode(["status"=>"error","message"=>"Estudiante no encontrado"]);
            return;
        }

        $id_usuario = $est['id_usuarioE'] ?? null;
        if (!$id_usuario) {
            echo json_encode(["status"=>"error","message"=>"Usuario asociado no encontrado"]);
            return;
        }

        // generar contraseña temporal formato ECO-XXXX (4 dígitos aleatorios)
        $random = random_int(1000, 9999);
        $tempPass = "ECO-" . $random;
        $hash = password_hash($tempPass, PASSWORD_BCRYPT);

        try {
            $ok = $estModel->actualizarPasswordUsuario($id_usuario, $hash);
            if ($ok) {
                // retornamos la contraseña temporal para mostrar al coordinador
                echo json_encode(["status"=>"success","message"=>"Contraseña restablecida","temp_password"=>$tempPass]);
            } else {
                echo json_encode(["status"=>"error","message"=>"No se pudo restablecer"]);
            }
        } catch (Exception $e) {
            echo json_encode(["status"=>"error","message"=>"Error interno"]);
        }
    }
}
