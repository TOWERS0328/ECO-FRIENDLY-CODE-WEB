<?php
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

            $id_usuario = $usuarioModel->crearUsuario(
                $data['correo'],
                $passwordHash,
                'estudiante'
            );

            if (!$id_usuario) {
                echo json_encode(["status" => "error", "message" => "Error creando usuario"]);
                return;
            }

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
        echo json_encode($estModel->getAllEstudiantes());
    }

    public function actualizar()
    {
        header("Content-Type: application/json");

        if (!isset($_POST['id_estudiante'])) {
            echo json_encode(["status" => "error", "message" => "id_estudiante es requerido"]);
            return;
        }

        $data = $_POST;
        $required = ["id_estudiante", "nombre", "apellido", "genero", "cedula", "carrera"];

        foreach ($required as $f) {
            if (!isset($data[$f]) || trim($data[$f]) === "") {
                echo json_encode(["status" => "error", "field" => $f, "message" => "El campo $f es obligatorio"]);
                return;
            }
        }

        $estModel = new Estudiante();
        $usuarioModel = new Usuario();

        $est = $estModel->getByIdEstudiante($data['id_estudiante']);
        if (!$est) {
            echo json_encode(["status" => "error", "message" => "Estudiante no encontrado"]);
            return;
        }

        $id_usuario = $est["id_usuarioE"];

        if ($estModel->cedulaExisteEnOtro($data["cedula"], $data["id_estudiante"])) {
            echo json_encode(["status" => "error", "field" => "cedula", "message" => "La cédula ya pertenece a otro estudiante"]);
            return;
        }

        if (isset($data["correo"]) && $data["correo"] !== "") {
            if (!filter_var($data["correo"], FILTER_VALIDATE_EMAIL)) {
                echo json_encode(["status" => "error", "field" => "correo", "message" => "Correo no válido"]);
                return;
            }

            if ($estModel->correoExisteEnOtro($data["correo"], $id_usuario)) {
                echo json_encode(["status" => "error", "field" => "correo", "message" => "El correo ya está registrado por otro usuario"]);
                return;
            }
        }

        $fotoPath = null;

        if (!empty($_FILES["foto"]["name"])) {
            $uploadDir = __DIR__ . "/../../uploads/estudiantes/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $ext  = pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION);
            $name = time() . "_" . bin2hex(random_bytes(6)) . "." . $ext;
            $dest = $uploadDir . $name;

            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $dest)) {
                $fotoPath = "uploads/estudiantes/" . $name;
            }
        }

        /* ------------ ACTUALIZAR ------------ */
        try {
            $estModel->actualizarPerfilEstudiante(
                $data["id_estudiante"],
                $data["nombre"],
                $data["apellido"],
                $data["genero"],
                $data["cedula"],
                $data["carrera"]
            );

            if (isset($data["correo"]) && $data["correo"] !== "") {
                $estModel->actualizarCorreoUsuario($id_usuario, $data["correo"]);
            }

            if ($fotoPath) {
                $estModel->actualizarFotoPerfil($data["id_estudiante"], $fotoPath);
            }

            echo json_encode(["status" => "success", "message" => "Estudiante actualizado correctamente"]);
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Error interno"]);
        }
    }


    /* -------------------------------------------------
     *  RESTABLECER CONTRASEÑA
     * -------------------------------------------------*/
    public function restablecer()
    {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data || !isset($data["id_estudiante"])) {
            echo json_encode(["status" => "error", "message" => "id_estudiante es requerido"]);
            return;
        }

        $estModel = new Estudiante();
        $est = $estModel->getByIdEstudiante($data["id_estudiante"]);

        if (!$est) {
            echo json_encode(["status" => "error", "message" => "Estudiante no encontrado"]);
            return;
        }

        $id_usuario = $est["id_usuarioE"];

        $tempPass = "ECO-" . random_int(1000, 9999);
        $hash     = password_hash($tempPass, PASSWORD_BCRYPT);

        if ($estModel->actualizarPasswordUsuario($id_usuario, $hash)) {
            echo json_encode([
                "status" => "success",
                "message" => "Contraseña restablecida",
                "temp_password" => $tempPass
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "No se pudo restablecer"]);
        }
    }


    /* -------------------------------------------------
     *  ACTUALIZAR CONTRASEÑA (POR EL ESTUDIANTE)
     * -------------------------------------------------*/
    public function actualizarContrasena()
    {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data ||
            !isset($data["id_estudiante"]) ||
            !isset($data["actual"]) ||
            !isset($data["nueva"]) ||
            !isset($data["confirmar"])) 
        {
            echo json_encode(["status" => "error", "message" => "Faltan datos"]);
            return;
        }

        if ($data["nueva"] !== $data["confirmar"]) {
            echo json_encode(["status" => "error", "message" => "Las contraseñas no coinciden"]);
            return;
        }

        $estModel = new Estudiante();
        $usuarioModel = new Usuario();

        $est = $estModel->getByIdEstudiante($data["id_estudiante"]);
        if (!$est) {
            echo json_encode(["status" => "error", "message" => "Estudiante no encontrado"]);
            return;
        }

        $id_usuario = $est["id_usuarioE"];
        $usuario = $usuarioModel->getById($id_usuario);

        if (!$usuario) {
            echo json_encode(["status" => "error", "message" => "Usuario no encontrado"]);
            return;
        }

        if (!password_verify($data["actual"], $usuario["password"])) {
            echo json_encode(["status" => "error", "field" => "actual", "message" => "Contraseña actual incorrecta"]);
            return;
        }

        $hash = password_hash($data["nueva"], PASSWORD_BCRYPT);

        if ($usuarioModel->actualizarPassword($id_usuario, $hash)) {
            echo json_encode(["status" => "success", "message" => "Contraseña actualizada correctamente"]);
        } else {
            echo json_encode(["status" => "error", "message" => "No se pudo actualizar la contraseña"]);
        }
    }

    public function obtenerPorId() {
    header("Content-Type: application/json");

    if (!isset($_GET["id_estudiante"])) {
        echo json_encode(["status" => "error", "message" => "id_estudiante requerido"]);
        return;
    }

    $model = new Estudiante();
    $est = $model->getByIdEstudiante($_GET["id_estudiante"]);

    if (!$est) {
        echo json_encode(["status" => "error", "message" => "Estudiante no encontrado"]);
        return;
    }

    echo json_encode(["status" => "success", "data" => $est]);
}

}
?>
