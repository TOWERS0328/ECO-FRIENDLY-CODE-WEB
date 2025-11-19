<?php
require_once __DIR__ . '/../config/Database.php';

class Acopio {
    private $conn;
    private $tableAcopio = "tb_acopio";
    private $tableDetalle = "tb_detalle_acopio";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Obtener acopio pendiente de un estudiante
    public function getPendiente($id_estudiante) {
        $sql = "SELECT * FROM {$this->tableAcopio} 
                WHERE id_estudianteA = :id_estudiante AND estado = 'pendiente' LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_estudiante' => $id_estudiante]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear un acopio pendiente nuevo
    public function crearAcopio($id_estudiante) {
        $sql = "INSERT INTO {$this->tableAcopio} (id_estudianteA, puntos_totales) VALUES (:id_estudiante, 0)";
        $stmt = $this->conn->prepare($sql);
        if ($stmt->execute([':id_estudiante' => $id_estudiante])) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Agregar residuo al detalle del acopio
    public function agregarDetalle($id_acopio, $id_residuo, $cantidad, $puntos) {
        // Revisar si ya existe el residuo en el detalle
        $sqlCheck = "SELECT * FROM {$this->tableDetalle} 
                     WHERE id_acopioD = :id_acopio AND id_residuoD = :id_residuo LIMIT 1";
        $stmt = $this->conn->prepare($sqlCheck);
        $stmt->execute([
            ':id_acopio' => $id_acopio,
            ':id_residuo' => $id_residuo
        ]);
        $existe = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existe) {
            // Actualizar cantidad y puntos
            $nuevaCantidad = $existe['cantidad'] + $cantidad;
            $nuevosPuntos = $existe['puntos'] + $puntos;
            $sqlUpdate = "UPDATE {$this->tableDetalle} SET cantidad = :cantidad, puntos = :puntos WHERE id_detalle = :id_detalle";
            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            return $stmtUpdate->execute([
                ':cantidad' => $nuevaCantidad,
                ':puntos' => $nuevosPuntos,
                ':id_detalle' => $existe['id_detalle']
            ]);
        } else {
            // Insertar nuevo detalle
            $sqlInsert = "INSERT INTO {$this->tableDetalle} (id_acopioD, id_residuoD, cantidad, puntos)
                          VALUES (:id_acopio, :id_residuo, :cantidad, :puntos)";
            $stmtInsert = $this->conn->prepare($sqlInsert);
            return $stmtInsert->execute([
                ':id_acopio' => $id_acopio,
                ':id_residuo' => $id_residuo,
                ':cantidad' => $cantidad,
                ':puntos' => $puntos
            ]);
        }
    }

    // Listar todos los detalles de un acopio
    public function getDetalles($id_acopio) {
        $sql = "SELECT da.id_detalle, da.id_residuoD, da.cantidad, da.puntos, r.nombre, r.tipo, r.imagen
                FROM {$this->tableDetalle} da
                INNER JOIN tb_residuos r ON da.id_residuoD = r.id_residuo
                WHERE da.id_acopioD = :id_acopio";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_acopio' => $id_acopio]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Eliminar un residuo de la canasta
    public function eliminarDetalle($id_detalle) {
        $sql = "DELETE FROM {$this->tableDetalle} WHERE id_detalle = :id_detalle";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id_detalle' => $id_detalle]);
    }

    // Actualizar puntos totales del acopio
    public function actualizarPuntosTotales($id_acopio) {
        $sql = "SELECT SUM(puntos) as total FROM {$this->tableDetalle} WHERE id_acopioD = :id_acopio";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_acopio' => $id_acopio]);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        $sqlUpdate = "UPDATE {$this->tableAcopio} SET puntos_totales = :total WHERE id_acopio = :id_acopio";
        $stmtUpdate = $this->conn->prepare($sqlUpdate);
        $stmtUpdate->execute([
            ':total' => $total,
            ':id_acopio' => $id_acopio
        ]);
        return $total;
    }

    // Finalizar acopio (cambia estado a pendiente para validar)
    public function finalizarAcopio($id_acopio) {
        $sql = "UPDATE {$this->tableAcopio} SET estado = 'pendiente' WHERE id_acopio = :id_acopio";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id_acopio' => $id_acopio]);
    }
}
?>
