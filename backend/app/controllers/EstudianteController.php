<?php

require_once __DIR__ . '/../models/Estudiante.php';

class EstudianteController {

    public function registrar() {

        // Recibir JSON
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data) {
            echo json_encode([
                "status" => "error", 
                "field" => null,
                "message" => "No se recibieron datos JSON"
            ]);
            return;
        }

        // Campos necesarios
        $required = ["cedula", "nombre", "apellido", "correo", "contrasena", "programa", "confirmar"];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                echo json_encode([
                    "status" => "error", 
                    "field" => $field,
                    "message" => "El campo $field es obligatorio"
                ]);
                return;
            }
        }

        // Validación contraseñas
        if ($data["contrasena"] !== $data["confirmar"]) {
            echo json_encode([
                "status" => "error",
                "field" => "contrasena", // o "confirmar", o ambos en el frontend
                "message" => "Las contraseñas no coinciden"
            ]);
            return;
        }

        // Validación formato correo
        if (!filter_var($data["correo"], FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "status" => "error",
                "field" => "correo",
                "message" => "Correo no válido"
            ]);
            return;
        }

        $model = new Estudiante();

        // Cédula duplicada
        if ($model->existeCedula($data["cedula"])) {
            echo json_encode([
                "status" => "error",
                "field" => "cedula",
                "message" => "La cédula ya está registrada"
            ]);
            return;
        }

        // Correo duplicado
        if ($model->existeCorreo($data["correo"])) {
            echo json_encode([
                "status" => "error",
                "field" => "correo",
                "message" => "El correo ya está registrado"
            ]);
            return;
        }

        // Registrar estudiante
        $ok = $model->registrar($data);

        if ($ok) {
            echo json_encode([
                "status" => "success",
                "field" => null,
                "message" => "Estudiante registrado correctamente"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "field" => null,
                "message" => "Error al registrar"
            ]);
        }
    }
}
