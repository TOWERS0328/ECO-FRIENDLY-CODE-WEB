<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Estudiante.php';
require_once __DIR__ . '/../models/CoordinadorAmbiental.php';
require_once __DIR__ . '/../models/EntidadRecicladora.php';
require_once __DIR__ . '/../models/Asistente.php';

class LoginController {

    public function login() {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);

        $correo = $data['correo'] ?? null;
        $contrasena = $data['contrasena'] ?? null;

        if (!$correo || !$contrasena) {
            echo json_encode(["status" => "error", "message" => "Correo y contraseña son obligatorios"]);
            return;
        }

        $usuarioModel = new Usuario();
        $user = $usuarioModel->getByCorreo($correo);

        if (!$user) {
            echo json_encode(["status" => "error", "message" => "Correo o contraseña incorrectos"]);
            return;
        }

        // Verificar estado del usuario
        if ($user['estado'] != 1) {
            echo json_encode(["status" => "error", "message" => "Usuario inactivo"]);
            return;
        }

        // Verificar contraseña
        if (!password_verify($contrasena, $user['password'])) {
            echo json_encode(["status" => "error", "message" => "Correo o contraseña incorrectos"]);
            return;
        }

        // Obtener perfil según rol
        $perfil = null;
        switch ($user['rol']) {
            case 'estudiante':
                $perfil = (new Estudiante())->getPerfilS($user['id_usuario']);
                break;
            case 'coordinador':
                $perfil = (new CoordinadorAmbiental())->getPerfil($user['id_usuario']);
                break;
            case 'entidad':
                $perfil = (new EntidadRecicladora())->getPerfil($user['id_usuario']);
                break;
            case 'asistente':
                $perfil = (new Asistente())->getPerfil($user['id_usuario']);
                break;
            default:
                echo json_encode(["status" => "error", "message" => "Rol no reconocido"]);
                return;
        }

        if (!$perfil) {
            echo json_encode(["status" => "error", "message" => "Perfil no encontrado"]);
            return;
        }

        // Responder con éxito
        echo json_encode([
            "status" => "success",
            "rol" => $user['rol'],
            "usuario" => $perfil
        ]);
    }
}
