<?php
require_once __DIR__ . '/../config/Database.php';

class Acopio {
    private $conn;

    // TABLAS REALES
    private $tableCanasta = "tb_canasta_temp";
    private $tableResiduo = "tb_residuos";
    private $tableAcopio = "tb_acopio";
    private $tableDetalle = "tb_detalle_acopio";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();

        // Recomiendo activar excepciones para debugging (opcional)
        // $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // ================================
    // 1. LISTAR CANASTA TEMPORAL
    // ================================
    public function listarCanasta($id_estudiante) {
        $sql = "SELECT c.id_detalle_temp, c.id_residuoCa AS id_residuo, c.cantidad,
                       r.nombre, r.tipo, r.puntos AS puntos_unitarios, r.imagen
                FROM {$this->tableCanasta} c
                INNER JOIN {$this->tableResiduo} r ON c.id_residuoCa = r.id_residuo
                WHERE c.id_estudianteCa = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_estudiante]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ================================
    // 2. AGREGAR RESIDUO A CANASTA
    // ================================
    public function agregarCanasta($id_estudiante, $id_residuo, $cantidad) {

        // Verificar si ya existe en canasta
        $sql = "SELECT * FROM {$this->tableCanasta}
                WHERE id_estudianteCa = ? AND id_residuoCa = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_estudiante, $id_residuo]);

        if ($stmt->rowCount() > 0) {
            // Si ya existe → solo aumentar cantidad
            $sqlUpdate = "UPDATE {$this->tableCanasta}
                          SET cantidad = cantidad + ?
                          WHERE id_estudianteCa = ? AND id_residuoCa = ?";
            $stmt2 = $this->conn->prepare($sqlUpdate);
            return $stmt2->execute([$cantidad, $id_estudiante, $id_residuo]);

        } else {
            // Insertar nuevo
            $sqlInsert = "INSERT INTO {$this->tableCanasta}
                          (id_estudianteCa, id_residuoCa, cantidad)
                          VALUES (?, ?, ?)";
            $stmt2 = $this->conn->prepare($sqlInsert);
            return $stmt2->execute([$id_estudiante, $id_residuo, $cantidad]);
        }
    }

    public function limpiarCanasta($id_estudiante) {
        $sql = "DELETE FROM {$this->tableCanasta}
                WHERE id_estudianteCa = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id_estudiante]);
    }

    public function crearAcopio($id_estudiante, $puntosTotales) {
        $sql = "INSERT INTO {$this->tableAcopio}
                (id_estudianteA, fecha, estado, puntos_totales)
                VALUES (?, NOW(), 'pendiente', ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_estudiante, $puntosTotales]);
        return $this->conn->lastInsertId();
    }

    public function insertarDetalle($id_acopio, $id_residuo, $cantidad, $puntos) {
        $sql = "INSERT INTO {$this->tableDetalle}
                (id_acopioD, id_residuoD, cantidad, puntos)
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id_acopio, $id_residuo, $cantidad, $puntos]);
    }

    public function obtenerPuntosResiduo($id_residuo) {
        $sql = "SELECT puntos FROM {$this->tableResiduo} WHERE id_residuo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_residuo]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row["puntos"] : 0;
    }

    public function actualizarCantidad($id_estudiante, $id_residuo, $cantidad) {

        if ($cantidad <= 0) {
            // si la cantidad es 0 o menos, eliminar el item
            return $this->eliminarItem($id_estudiante, $id_residuo);
        }

        $sql = "UPDATE {$this->tableCanasta}
                SET cantidad = ?
                WHERE id_estudianteCa = ? AND id_residuoCa = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$cantidad, $id_estudiante, $id_residuo]);
    }

    public function eliminarItem($id_estudiante, $id_residuo) {
        $sql = "DELETE FROM {$this->tableCanasta}
                WHERE id_estudianteCa = ? AND id_residuoCa = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id_estudiante, $id_residuo]);
    }

    public function actualizarEstado($id_acopio, $nuevoEstado)
{
    $estados_validos = ['pendiente', 'validado', 'rechazado'];
    if (!in_array($nuevoEstado, $estados_validos)) {
        return ["status" => "error", "message" => "Estado inválido"];
    }

    $sql = "SELECT id_estudianteA, puntos_totales, estado 
            FROM {$this->tableAcopio} 
            WHERE id_acopio = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$id_acopio]);
    $acopio = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$acopio) {
        return ["status" => "error", "message" => "Acopio no encontrado"];
    }

    if ($acopio['estado'] !== 'pendiente') {
        return [
            "status" => "error",
            "message" => "Este acopio ya fue procesado y no se puede cambiar nuevamente."
        ];
    }

    $sqlUpdate = "UPDATE {$this->tableAcopio} SET estado = ? WHERE id_acopio = ?";
    $stmtUpdate = $this->conn->prepare($sqlUpdate);
    $stmtUpdate->execute([$nuevoEstado, $id_acopio]);

    if ($nuevoEstado === 'validado') {
        $sqlPuntos = "
            UPDATE tb_estudiantes 
            SET puntos_acumulados = puntos_acumulados + ? 
            WHERE id_estudiante = ?
        ";
        $stmtPuntos = $this->conn->prepare($sqlPuntos);
        $stmtPuntos->execute([
            $acopio['puntos_totales'],
            $acopio['id_estudianteA']
        ]);
    }

    return ["status" => "success", "message" => "Estado actualizado correctamente"];
}


    public function listarAcopios($id_estudiante) {
        $sql = "
            SELECT 
                a.fecha,
                r.tipo,
                r.nombre AS nombre_residuo,
                d.cantidad,
                d.puntos AS puntos_ganados,
                a.estado
            FROM {$this->tableAcopio} a
            INNER JOIN {$this->tableDetalle} d ON a.id_acopio = d.id_acopioD
            INNER JOIN {$this->tableResiduo} r ON r.id_residuo = d.id_residuoD
            WHERE a.id_estudianteA = ?
            ORDER BY a.fecha DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_estudiante]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarAcopiosParaAsistente() {
        $sql = "
            SELECT 
                a.id_acopio,
                e.nombre AS nombre_estudiante,
                e.apellido AS apellido_estudiante,
                e.cedula,
                a.fecha,
                a.estado,
                a.puntos_totales
            FROM {$this->tableAcopio} a
            INNER JOIN tb_estudiantes e ON e.id_estudiante = a.id_estudianteA
            ORDER BY a.fecha DESC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $acopios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Traer detalles de cada acopio (residuos)
        foreach ($acopios as &$acopio) {
            $sqlDetalle = "
                SELECT r.nombre AS nombre_residuo, r.tipo, d.cantidad, d.puntos
                FROM {$this->tableDetalle} d
                INNER JOIN {$this->tableResiduo} r ON r.id_residuo = d.id_residuoD
                WHERE d.id_acopioD = ?
            ";
            $stmtDetalle = $this->conn->prepare($sqlDetalle);
            $stmtDetalle->execute([$acopio['id_acopio']]);
            $acopio['residuos'] = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);
        }

        return $acopios;
    }

    public function contarPorEstado($estado) {
        $sql = "SELECT COUNT(*) as total FROM {$this->tableAcopio} WHERE estado = :estado";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':estado' => $estado]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }

}
?>
