<?php

require_once __DIR__ . '/../config/Database.php';

class Estudiante {

    private $conn;
    private $table = "estudiantes";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function registrar($data) {
        $sql = "INSERT INTO $this->table 
                (cedula, nombre, apellido, correo, contrasena, programa)
                VALUES 
                (:cedula, :nombre, :apellido, :correo, :contrasena, :programa)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':cedula' => $data['cedula'],
            ':nombre' => $data['nombre'],
            ':apellido' => $data['apellido'],
            ':correo' => $data['correo'],
            ':contrasena' => password_hash($data['contrasena'], PASSWORD_BCRYPT),
            ':programa' => $data['programa']
        ]);
    }

    public function existeCedula($cedula) {
        $sql = "SELECT cedula FROM $this->table WHERE cedula = :cedula LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':cedula' => $cedula]);
        return $stmt->fetch() ? true : false;
    }

    public function existeCorreo($correo) {
        $sql = "SELECT correo FROM $this->table WHERE correo = :correo LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':correo' => $correo]);
        return $stmt->fetch() ? true : false;
    }
}
