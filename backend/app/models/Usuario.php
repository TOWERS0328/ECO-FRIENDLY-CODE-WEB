<?php
require_once __DIR__ . '/../config/Database.php';

class Usuario {
    private $conn;
    private $table = "tb_usuarios";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function existeCorreo($correo) {
        $sql = "SELECT id_usuario FROM {$this->table} WHERE correo = :correo LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':correo' => $correo]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }

    public function crearUsuario($correo, $passwordHash, $rol = 'estudiante') {
        $sql = "INSERT INTO {$this->table} (correo, password, rol) VALUES (:correo, :password, :rol)";
        $stmt = $this->conn->prepare($sql);
        $ok = $stmt->execute([
            ':correo' => $correo,
            ':password' => $passwordHash,
            ':rol' => $rol
        ]);
        if (!$ok) return false;
        return $this->conn->lastInsertId(); // id_usuario
    }

    public function getByCorreo($correo) {
        $sql = "SELECT * FROM {$this->table} WHERE correo = :correo LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':correo' => $correo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Opcional: obtener usuario por id
    public function getById($id_usuario) {
        $sql = "SELECT * FROM {$this->table} WHERE id_usuario = :id_usuario LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_usuario' => $id_usuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
