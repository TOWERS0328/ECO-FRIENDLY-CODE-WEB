<?php
require_once __DIR__ . '/../config/Database.php';

class Premio {
    private $conn;
    private $table = "tb_premios";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllPremios() {
    $sql = "SELECT 
                p.id_premio,
                p.codigo,
                p.nombre,
                p.puntos_requeridos,
                p.stock,
                p.imagen,
                p.id_empresaP,
                e.nombre AS empresa,
                p.estado
            FROM {$this->table} p
            LEFT JOIN tb_empresas_patrocinadoras e 
            ON p.id_empresaP = e.id_empresa";
    
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    // Registrar premio
    public function crearPremio($codigo, $nombre, $puntos, $stock, $imagen, $id_empresa) {
    $sql = "INSERT INTO {$this->table} 
            (codigo, nombre, puntos_requeridos, stock, imagen, id_empresaP) 
            VALUES (:codigo, :nombre, :puntos, :stock, :imagen, :id_empresa)";
    $stmt = $this->conn->prepare($sql);
    return $stmt->execute([
        ':codigo' => $codigo,
        ':nombre' => $nombre,
        ':puntos' => $puntos,
        ':stock' => $stock,
        ':imagen' => $imagen,
        ':id_empresa' => $id_empresa
    ]);
}

public function actualizarPremio($id_premio, $nombre, $puntos, $stock, $imagen, $id_empresa, $estado) {
    if ($imagen !== null) {
        $sql = "UPDATE {$this->table} SET 
                    nombre = :nombre,
                    puntos_requeridos = :puntos,
                    stock = :stock,
                    imagen = :imagen,
                    id_empresaP = :id_empresa,
                    estado = :estado
                WHERE id_premio = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id' => $id_premio,
            ':nombre' => $nombre,
            ':puntos' => $puntos,
            ':stock' => $stock,
            ':imagen' => $imagen,
            ':id_empresa' => $id_empresa,
            ':estado' => $estado
        ]);
    } else {
        $sql = "UPDATE {$this->table} SET 
                    nombre = :nombre,
                    puntos_requeridos = :puntos,
                    stock = :stock,
                    id_empresaP = :id_empresa,
                    estado = :estado
                WHERE id_premio = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id' => $id_premio,
            ':nombre' => $nombre,
            ':puntos' => $puntos,
            ':stock' => $stock,
            ':id_empresa' => $id_empresa,
            ':estado' => $estado
        ]);
    }
}


    // Obtener un premio por id
    public function getById($id_premio) {
        $sql = "SELECT * FROM {$this->table} WHERE id_premio = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id_premio]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar premio
   /* public function actualizarPremio($id_premio, $nombre, $puntos, $stock, $imagen, $id_empresa, $estado) {
        $sql = "UPDATE {$this->table} SET 
                    nombre = :nombre,
                    puntos_requeridos = :puntos,
                    stock = :stock,
                    imagen = :imagen,
                    id_empresaP = :id_empresa,
                    estado = :estado
                WHERE id_premio = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id' => $id_premio,
            ':nombre' => $nombre,
            ':puntos' => $puntos,
            ':stock' => $stock,
            ':imagen' => $imagen,
            ':id_empresa' => $id_empresa,
            ':estado' => $estado
        ]);
    }*/
}
