<?php
require_once __DIR__ . '/../config/Database.php';

class Empresa {
    private $conn;
    private $table = "tb_empresas_patrocinadoras";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // 1️⃣ Listar todas las empresas
    public function getEmpresasActivas() {
        $sql = "SELECT id_empresa, nit, nombre, logo, contacto, estado 
                FROM {$this->table} 
                WHERE estado = 'activo'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2️⃣ Registrar nueva empresa
    public function registrar($nit, $nombre, $logo, $contacto) {
        $sql = "INSERT INTO {$this->table} (nit, nombre, logo, contacto, estado) 
                VALUES (:nit, :nombre, :logo, :contacto, 'activo')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nit', $nit);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':logo', $logo);
        $stmt->bindParam(':contacto', $contacto);
        if($stmt->execute()){
            return ["status"=>"success", "message"=>"Empresa registrada correctamente"];
        } else {
            return ["status"=>"error", "message"=>"Error al registrar empresa"];
        }
    }

    // 3️⃣ Editar empresa existente
    public function actualizar($id_empresa, $nit, $nombre, $logo, $contacto, $estado) {
        $sql = "UPDATE {$this->table} 
                SET nit = :nit, nombre = :nombre, logo = :logo, contacto = :contacto, estado = :estado
                WHERE id_empresa = :id_empresa";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_empresa', $id_empresa);
        $stmt->bindParam(':nit', $nit);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':logo', $logo);
        $stmt->bindParam(':contacto', $contacto);
        $stmt->bindParam(':estado', $estado);
        if($stmt->execute()){
            return ["status"=>"success", "message"=>"Empresa actualizada correctamente"];
        } else {
            return ["status"=>"error", "message"=>"Error al actualizar empresa"];
        }
    }

    // 4️⃣ Obtener empresa por ID
    public function getEmpresaById($id_empresa) {
        $sql = "SELECT * FROM {$this->table} WHERE id_empresa = :id_empresa";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_empresa', $id_empresa);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getEmpresas() {
    $sql = "SELECT id_empresa, nit, nombre, logo, contacto, estado 
            FROM {$this->table}";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}

