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
            // Si ya existe, actualizar cantidad y puntos
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

    // Actualizar puntos totales del acopio (solo suman para el acopio, no al estudiante aún)
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

    // Finalizar acopio (solo cambia a pendiente para validación, el asistente validará después)
    public function finalizarAcopio($id_acopio) {
        $sql = "UPDATE {$this->tableAcopio} SET estado = 'pendiente' WHERE id_acopio = :id_acopio";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id_acopio' => $id_acopio]);
    }

    // Validar acopio (solo el asistente ambiental puede cambiar a 'validado')
    public function validarAcopio($id_acopio) {
        // Primero obtenemos el total de puntos
        $sqlTotal = "SELECT puntos_totales, id_estudianteA FROM {$this->tableAcopio} WHERE id_acopio = :id_acopio";
        $stmtTotal = $this->conn->prepare($sqlTotal);
        $stmtTotal->execute([':id_acopio' => $id_acopio]);
        $acopio = $stmtTotal->fetch(PDO::FETCH_ASSOC);

        if (!$acopio) return false;

        // Actualizar el estado a 'validado'
        $sqlUpdate = "UPDATE {$this->tableAcopio} SET estado = 'validado' WHERE id_acopio = :id_acopio";
        $stmtUpdate = $this->conn->prepare($sqlUpdate);
        $ok = $stmtUpdate->execute([':id_acopio' => $id_acopio]);

        // Si se validó correctamente, sumar puntos al estudiante
        if ($ok) {
            $sqlEstudiante = "UPDATE tb_estudiantes 
                              SET puntos_acumulados = puntos_acumulados + :puntos 
                              WHERE id_estudiante = :id_estudiante";
            $stmtEst = $this->conn->prepare($sqlEstudiante);
            $stmtEst->execute([
                ':puntos' => $acopio['puntos_totales'],
                ':id_estudiante' => $acopio['id_estudianteA']
            ]);
        }

        return $ok;
    }

    // Listar acopios de un estudiante (pendientes o validados)
    public function listarAcopiosPorEstado($id_estudiante, $estado = null) {
        $sql = "SELECT * FROM {$this->tableAcopio} WHERE id_estudianteA = :id_estudiante";
        if ($estado) {
            $sql .= " AND estado = :estado";
        }
        $stmt = $this->conn->prepare($sql);
        $params = [':id_estudiante' => $id_estudiante];
        if ($estado) $params[':estado'] = $estado;
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
