<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Estudiante.php';
require_once __DIR__ . '/../models/CoordinadorAmbiental.php';
require_once __DIR__ . '/../models/EntidadRecicladora.php';

class LoginController {

    public function login() {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);

        $correo = $data['correo'] ?? null;
        $contrasena = $data['contrasena'] ?? null;

        if (!$correo || !$contrasena) {
            echo json_encode(["status"=>"error","message"=>"Correo y contraseña son obligatorios"]);
            return;
        }

        $usuarioModel = new Usuario();
        $user = $usuarioModel->getByCorreo($correo);

        if (!$user || !password_verify($contrasena, $user['password'])) {
            echo json_encode(["status"=>"error","message"=>"Correo o contraseña incorrectos"]);
            return;
        }

        // Obtener perfil según rol
        $perfil = null;
        switch($user['rol']){
            case 'estudiante':
                $perfil = (new Estudiante())->getPerfil($user['id_usuario']);
                break;
            case 'coordinador':
                $perfil = (new CoordinadorAmbiental())->getPerfil($user['id_usuario']);
                break;
            case 'entidad':
                $perfil = (new EntidadRecicladora())->getPerfil($user['id_usuario']);
                break;
        }

        echo json_encode([
            "status" => "success",
            "rol" => $user['rol'],
            "usuario" => $perfil
        ]);
    }
}
