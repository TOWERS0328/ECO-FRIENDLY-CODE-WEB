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
    }

    // ================================
    // 1. LISTAR CANASTA TEMPORAL
    // ================================
    public function listarCanasta($id_estudiante) {
        $sql = "SELECT c.id_detalle_temp, c.id_residuoCa AS id_residuo, c.cantidad,
                       r.nombre, r.tipo, r.puntos AS puntos_unitarios, r.imagen
                FROM $this->tableCanasta c
                INNER JOIN $this->tableResiduo r ON c.id_residuoCa = r.id_residuo
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
        $sql = "SELECT * FROM $this->tableCanasta
                WHERE id_estudianteCa = ? AND id_residuoCa = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_estudiante, $id_residuo]);

        if ($stmt->rowCount() > 0) {
            // Si ya existe → solo aumentar cantidad
            $sqlUpdate = "UPDATE $this->tableCanasta
                          SET cantidad = cantidad + ?
                          WHERE id_estudianteCa = ? AND id_residuoCa = ?";
            $stmt2 = $this->conn->prepare($sqlUpdate);
            return $stmt2->execute([$cantidad, $id_estudiante, $id_residuo]);

        } else {
            // Insertar nuevo
            $sqlInsert = "INSERT INTO $this->tableCanasta
                          (id_estudianteCa, id_residuoCa, cantidad)
                          VALUES (?, ?, ?)";
            $stmt2 = $this->conn->prepare($sqlInsert);
            return $stmt2->execute([$id_estudiante, $id_residuo, $cantidad]);
        }
    }

    // ================================
    // 3. LIMPIAR CANASTA
    // ================================
    public function limpiarCanasta($id_estudiante) {
        $sql = "DELETE FROM $this->tableCanasta
                WHERE id_estudianteCa = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id_estudiante]);
    }

    // ================================
    // 4. CREAR ACOPIO
    // ================================
    public function crearAcopio($id_estudiante, $puntosTotales) {
        $sql = "INSERT INTO $this->tableAcopio
                (id_estudianteA, fecha, estado, puntos_totales)
                VALUES (?, NOW(), 'pendiente', ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_estudiante, $puntosTotales]);
        return $this->conn->lastInsertId();
    }

    // ================================
    // 5. INSERTAR DETALLE DEL ACOPIO
    // ================================
    public function insertarDetalle($id_acopio, $id_residuo, $cantidad, $puntos) {
        $sql = "INSERT INTO $this->tableDetalle
                (id_acopioD, id_residuoD, cantidad, puntos)
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id_acopio, $id_residuo, $cantidad, $puntos]);
    }

    // ================================
    // 6. OBTENER PUNTOS UNITARIOS
    // ================================
    public function obtenerPuntosResiduo($id_residuo) {
        $sql = "SELECT puntos FROM $this->tableResiduo WHERE id_residuo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_residuo]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row["puntos"] : 0;
    }
    // ================================
// 7. ACTUALIZAR CANTIDAD DE UN ITEM
// ================================
public function actualizarCantidad($id_estudiante, $id_residuo, $cantidad) {

    if ($cantidad <= 0) {
        // si la cantidad es 0 o menos, eliminar el item
        return $this->eliminarItem($id_estudiante, $id_residuo);
    }

    $sql = "UPDATE $this->tableCanasta
            SET cantidad = ?
            WHERE id_estudianteCa = ? AND id_residuoCa = ?";
    $stmt = $this->conn->prepare($sql);
    return $stmt->execute([$cantidad, $id_estudiante, $id_residuo]);
}

// ================================
// 8. ELIMINAR ITEM DE LA CANASTA
// ================================
public function eliminarItem($id_estudiante, $id_residuo) {
    $sql = "DELETE FROM $this->tableCanasta
            WHERE id_estudianteCa = ? AND id_residuoCa = ?";
    $stmt = $this->conn->prepare($sql);
    return $stmt->execute([$id_estudiante, $id_residuo]);
}

// ================================
// CAMBIAR ESTADO DEL ACOPIO
// ================================
public function actualizarEstado($id_acopio, $nuevo_estado) {
    // Validar que el estado sea uno de los permitidos
    $estados_validos = ['pendiente', 'validado', 'rechazado'];
    if (!in_array($nuevo_estado, $estados_validos)) {
        return false;
    }

    // 1. Obtener datos del acopio
    $sql = "SELECT id_estudianteAc, puntos_totales, estado FROM $this->tableAcopio WHERE id_acopio = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$id_acopio]);
    $acopio = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$acopio) return false;

    // 2. Actualizar el estado
    $sql = "UPDATE $this->tableAcopio SET estado = ? WHERE id_acopio = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$nuevo_estado, $id_acopio]);

    // 3. Otorgar puntos solo si pasa a "validado" y antes no estaba validado
    if ($nuevo_estado === 'validado' && $acopio['estado'] !== 'validado') {
        $sql = "UPDATE tb_estudiantes SET puntos = puntos + ? WHERE id_estudiante = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$acopio['puntos_totales'], $acopio['id_estudianteAc']]);
    }

    return true;
}


}
