<?php
require_once __DIR__ . '/../config/Database.php';

class Residuo {
    private $conn;
    private $table = "tb_residuos";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // --------------------------------------
    // GENERAR CÓDIGO AUTO (R001, R002…)
    // --------------------------------------
    private function generarCodigo() {
        $sql = "SELECT codigo FROM {$this->table} ORDER BY id_residuo DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $last = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($last && isset($last['codigo'])) {
            // Extrae número: R005 → 5
            $num = intval(substr($last['codigo'], 1)) + 1;
        } else {
            $num = 1; // Primer registro
        }

        return "R" . str_pad($num, 3, "0", STR_PAD_LEFT);
    }

    // Obtener todos los residuos
    public function getAllResiduos() {
        $sql = "SELECT * FROM {$this->table}";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Crear residuo (el código se genera automático)
    public function crearResiduo($nombre, $tipo, $puntos, $imagen, $estado) {

        $codigo = $this->generarCodigo();

        $sql = "INSERT INTO {$this->table} 
                (codigo, nombre, tipo, puntos, imagen, estado)
                VALUES (:codigo, :nombre, :tipo, :puntos, :imagen, :estado)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':codigo' => $codigo,
            ':nombre' => $nombre,
            ':tipo' => $tipo,
            ':puntos' => $puntos,
            ':imagen' => $imagen,
            ':estado' => $estado
        ]);
    }

    // Actualizar residuo (NO se modifica el código)
    public function actualizarResiduo($id_residuo, $nombre, $tipo, $puntos, $imagen, $estado) {
        if ($imagen !== null) {
            $sql = "UPDATE {$this->table} SET 
                        nombre = :nombre,
                        tipo = :tipo,
                        puntos = :puntos,
                        imagen = :imagen,
                        estado = :estado
                    WHERE id_residuo = :id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':id' => $id_residuo,
                ':nombre' => $nombre,
                ':tipo' => $tipo,
                ':puntos' => $puntos,
                ':imagen' => $imagen,
                ':estado' => $estado
            ]);
        } else {
            $sql = "UPDATE {$this->table} SET 
                        nombre = :nombre,
                        tipo = :tipo,
                        puntos = :puntos,
                        estado = :estado
                    WHERE id_residuo = :id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':id' => $id_residuo,
                ':nombre' => $nombre,
                ':tipo' => $tipo,
                ':puntos' => $puntos,
                ':estado' => $estado
            ]);
        }
    }

    // Obtener residuo por id
    public function getById($id_residuo) {
        $sql = "SELECT * FROM {$this->table} WHERE id_residuo = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id_residuo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getResiduosActivos() {
    $sql = "SELECT id_residuo, codigo, nombre, tipo, puntos, imagen
            FROM {$this->table}
            WHERE estado = 'Activo'";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}
