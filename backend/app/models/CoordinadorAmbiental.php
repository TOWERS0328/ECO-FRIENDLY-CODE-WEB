<?php
require_once __DIR__ . '/../config/Database.php';

class CoordinadorAmbiental {
    private $conn;
    private $table = "tb_coordinadores";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Crear perfil de coordinador
    public function crearPerfilCoordinador($id_usuarioC, $nombre, $apellido, $cedula, $telefono) {
        $sql = "INSERT INTO {$this->table} 
                (id_usuarioC, nombre, apellido, cedula, telefono)
                VALUES (:id_usuarioC, :nombre, :apellido, :cedula, :telefono)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id_usuarioC' => $id_usuarioC,
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':cedula' => $cedula,
            ':telefono' => $telefono
        ]);
    }

    // Obtener perfil de coordinador por id_usuario
   public function getPerfil($id_usuario) {
        $sql = "SELECT c.id_coordinador, c.nombre, c.apellido, c.cedula, c.telefono, u.correo
                FROM {$this->table} c
                JOIN tb_usuarios u ON u.id_usuario = c.id_usuarioC
                WHERE c.id_usuarioC = :id_usuario
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_usuario' => $id_usuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Opcional: verificar si cedula ya existe
    public function existeCedula($cedula) {
        $sql = "SELECT id_coordinador FROM {$this->table} WHERE cedula = :cedula LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':cedula' => $cedula]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }
}
