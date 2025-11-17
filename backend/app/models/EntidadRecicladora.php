<?php
require_once __DIR__ . '/../config/Database.php';

class EntidadRecicladora {
    private $conn;
    private $table = "tb_entidades_recicladoras";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Crear perfil de entidad recicladora
    public function crearPerfilEntidad($id_usuarioR, $nombre_entidad, $nit, $direccion, $telefono, $responsable) {
        $sql = "INSERT INTO {$this->table} 
                (id_usuarioR, nombre_entidad, nit, direccion, telefono, responsable)
                VALUES (:id_usuarioR, :nombre_entidad, :nit, :direccion, :telefono, :responsable)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id_usuarioR' => $id_usuarioR,
            ':nombre_entidad' => $nombre_entidad,
            ':nit' => $nit,
            ':direccion' => $direccion,
            ':telefono' => $telefono,
            ':responsable' => $responsable
        ]);
    }

    // Obtener perfil de entidad por id_usuario
    public function getPerfil($id_usuario) {
        $sql = "SELECT * FROM {$this->table} WHERE id_usuarioR = :id_usuario LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_usuario' => $id_usuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Opcional: verificar si nit ya existe
    public function existeNIT($nit) {
        $sql = "SELECT id_entidad FROM {$this->table} WHERE nit = :nit LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':nit' => $nit]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }
}
