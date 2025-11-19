<?php
require_once __DIR__ . '/../config/Database.php';

class Canje {
    private $conn;
    private $tableCanje = "tb_canje";
    private $tableDetalle = "tb_detalle_canje";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // 🔹 Crear un canje nuevo
    public function crearCanje($id_estudiante, $puntos_usados) {
        $sql = "INSERT INTO {$this->tableCanje} (id_estudianteC, puntos_usados) VALUES (:id_estudiante, :puntos)";
        $stmt = $this->conn->prepare($sql);
        if ($stmt->execute([':id_estudiante' => $id_estudiante, ':puntos' => $puntos_usados])) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // 🔹 Agregar detalle de premio al canje
    public function agregarDetalle($id_canje, $id_premio, $cantidad, $puntos) {
        $sql = "INSERT INTO {$this->tableDetalle} (id_canjeD, id_premioD, cantidad, puntos)
                VALUES (:id_canje, :id_premio, :cantidad, :puntos)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id_canje' => $id_canje,
            ':id_premio' => $id_premio,
            ':cantidad' => $cantidad,
            ':puntos' => $puntos
        ]);
    }

    // 🔹 Obtener detalles de un canje
    public function getDetalles($id_canje) {
        $sql = "SELECT dc.id_detalle, dc.id_premioD, dc.cantidad, dc.puntos, p.nombre, p.imagen
                FROM {$this->tableDetalle} dc
                INNER JOIN tb_premios p ON dc.id_premioD = p.id_premio
                WHERE dc.id_canjeD = :id_canje";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_canje' => $id_canje]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔹 Obtener canje pendiente de un estudiante
    public function getPendientePorEstudiante($id_estudiante) {
        $sql = "SELECT * FROM {$this->tableCanje} 
                WHERE id_estudianteC = :id_estudiante AND estado = 'pendiente' 
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_estudiante' => $id_estudiante]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 🔹 Actualizar puntos usados en el canje al confirmarlo
    public function actualizarPuntosUsados($id_canje, $puntos) {
        $sql = "UPDATE {$this->tableCanje} SET puntos_usados = :puntos WHERE id_canje = :id_canje";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':puntos' => $puntos,
            ':id_canje' => $id_canje
        ]);
    }

    // 🔹 Actualizar estado del canje (pendiente -> entregado)
    public function actualizarEstado($id_canje, $estado) {
        $sql = "UPDATE {$this->tableCanje} SET estado = :estado WHERE id_canje = :id_canje";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':estado' => $estado, ':id_canje' => $id_canje]);
    }

    // 🔹 Eliminar un detalle del carrito (antes de confirmar)
    public function eliminarDetalle($id_detalle) {
        $sql = "DELETE FROM {$this->tableDetalle} WHERE id_detalle = :id_detalle";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id_detalle' => $id_detalle]);
    }
}
?>
