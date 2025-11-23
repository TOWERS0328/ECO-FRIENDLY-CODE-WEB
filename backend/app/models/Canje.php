<?php
require_once __DIR__ . '/../config/Database.php';

class Canje {
    private $conn;

    private $tableCarrito = "tb_carrito_temp"; // carrito temporal
    private $tablePremios = "tb_premios";      // premios reales
    private $tableCanje = "tb_canje";          // canjes realizados
    private $tableDetalle = "tb_detalle_canje"; // detalle de cada canje

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // ================================
    // 1. LISTAR CARRITO TEMPORAL
    // ================================
    public function listarCarrito($id_estudiante)
{
    $sql = "SELECT c.id_detalle_temp, c.id_premioC AS id_premio, c.cantidad,
                   p.nombre, p.puntos_requeridos AS puntos_unitarios, p.imagen, p.stock
            FROM $this->tableCarrito c
            INNER JOIN $this->tablePremios p ON c.id_premioC = p.id_premio
            WHERE c.id_estudianteC = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$id_estudiante]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    // ================================
    // 2. AGREGAR AL CARRITO TEMPORAL
    // ================================
    public function agregarCarrito($id_estudiante, $id_premio, $cantidad)
    {
        $sql = "SELECT * FROM $this->tableCarrito 
                WHERE id_estudianteC = ? AND id_premioC = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_estudiante, $id_premio]);

        if ($stmt->rowCount() > 0) {
            $sqlUpdate = "UPDATE $this->tableCarrito 
                          SET cantidad = cantidad + ? 
                          WHERE id_estudianteC = ? AND id_premioC = ?";
            $stmt2 = $this->conn->prepare($sqlUpdate);
            return $stmt2->execute([$cantidad, $id_estudiante, $id_premio]);
        } else {
            $sqlInsert = "INSERT INTO $this->tableCarrito (id_estudianteC, id_premioC, cantidad) 
                          VALUES (?, ?, ?)";
            $stmt2 = $this->conn->prepare($sqlInsert);
            return $stmt2->execute([$id_estudiante, $id_premio, $cantidad]);
        }
    }

    // ================================
    // 3. ACTUALIZAR CANTIDAD EN CARRITO
    // ================================
    public function actualizarCantidad($id_estudiante, $id_premio, $cantidad)
    {
        if ($cantidad <= 0) return $this->eliminarItem($id_estudiante, $id_premio);

        $sql = "UPDATE $this->tableCarrito SET cantidad = ? WHERE id_estudianteC = ? AND id_premioC = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$cantidad, $id_estudiante, $id_premio]);
    }

    // ================================
    // 4. ELIMINAR ITEM DEL CARRITO
    // ================================
    public function eliminarItem($id_estudiante, $id_premio)
    {
        $sql = "DELETE FROM $this->tableCarrito WHERE id_estudianteC = ? AND id_premioC = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id_estudiante, $id_premio]);
    }

    // ================================
    // 5. LIMPIAR CARRITO
    // ================================
    public function limpiarCarrito($id_estudiante)
    {
        $sql = "DELETE FROM $this->tableCarrito WHERE id_estudianteC = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id_estudiante]);
    }

    // ================================
    // 6. FINALIZAR CANJE (verificar puntos)
    // ================================
    public function finalizarCanje($id_estudiante)
{
    // 1. Obtener carrito
    $carrito = $this->listarCarrito($id_estudiante);
    if (empty($carrito)) {
        return ["status" => "error", "message" => "El carrito está vacío"];
    }

    // 2. Calcular puntos totales y verificar stock
    $puntosTotales = 0;
    foreach ($carrito as $item) {
        // Stock
        if ((int)$item['cantidad'] > (int)$item['stock']) {
            return [
                "status" => "error",
                "message" => "No hay suficiente stock para '{$item['nombre']}'. Disponible: {$item['stock']}, intentaste canjear: {$item['cantidad']}"
            ];
        }

        // Puntos
        $puntosTotales += (int)$item['puntos_unitarios'] * (int)$item['cantidad'];
    }

    // 3. Obtener puntos acumulados del estudiante
    $sql = "SELECT puntos_acumulados FROM tb_estudiantes WHERE id_estudiante = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$id_estudiante]);
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$estudiante) {
        return ["status" => "error", "message" => "Estudiante no encontrado"];
    }

    $puntosEstudiante = (int)$estudiante["puntos_acumulados"];
    if ($puntosEstudiante < $puntosTotales) {
        return ["status" => "error", "message" => "Puntos insuficientes"];
    }

    // 4. Crear registro en tb_canje
    $sql = "INSERT INTO tb_canje (id_estudianteC, fecha, estado, puntos_usados) VALUES (?, NOW(), 'pendiente', ?)";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$id_estudiante, $puntosTotales]);
    $id_canje = $this->conn->lastInsertId();

    // 5. Insertar detalles y actualizar stock
    foreach ($carrito as $item) {
        $puntosItem = (int)$item['puntos_unitarios'] * (int)$item['cantidad'];

        // Insert detalle
        $sqlDet = "INSERT INTO tb_detalle_canje (id_canjeD, id_premioD, cantidad, puntos) VALUES (?, ?, ?, ?)";
        $stmtDet = $this->conn->prepare($sqlDet);
        $stmtDet->execute([$id_canje, $item['id_premio'], $item['cantidad'], $puntosItem]);

        // Reducir stock del premio
        $sqlStock = "UPDATE tb_premios SET stock = stock - ? WHERE id_premio = ?";
        $stmtStock = $this->conn->prepare($sqlStock);
        $stmtStock->execute([$item['cantidad'], $item['id_premio']]);
    }

    // 6. Restar puntos al estudiante
    $sql = "UPDATE tb_estudiantes SET puntos_acumulados = puntos_acumulados - ? WHERE id_estudiante = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$puntosTotales, $id_estudiante]);

    // 7. Limpiar carrito
    $this->limpiarCarrito($id_estudiante);

    // 8. Retornar resultado
    return [
        "status" => "success",
        "message" => "Canje realizado correctamente",
        "id_canje" => $id_canje,
        "puntos_usados" => $puntosTotales
    ];
}


    public function listarTodosLosCanjes($id_estudiante) {
        $sql = "
            SELECT 
                c.fecha,
                p.nombre AS nombre_premio,
                d.cantidad,
                d.puntos AS puntos_gastados,
                c.estado
            FROM tb_canje c
            INNER JOIN tb_detalle_canje d ON c.id_canje = d.id_canjeD
            INNER JOIN premios p ON p.id_premio = d.id_premioD
            WHERE c.id_estudianteC = ?
            ORDER BY c.fecha DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_estudiante]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPremiosPorCanje($id_canje) {
    $sql = "
        SELECT 
            p.id_premio,
            p.nombre,
            p.codigo,
            d.cantidad,
            d.puntos
        FROM tb_detalle_canje d
        INNER JOIN tb_premios p ON p.id_premio = d.id_premioD
        WHERE d.id_canjeD = :id_canje
    ";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id_canje', $id_canje, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function obtenerCanje($id_canje) {
    $sql = "
        SELECT 
            c.id_canje,
            c.fecha,
            c.estado,
            c.puntos_usados,
            e.id_estudiante,
            e.nombre AS nombre_estudiante,
            e.apellido AS apellido_estudiante,
            e.cedula
        FROM tb_canje c
        INNER JOIN tb_estudiantes e ON e.id_estudiante = c.id_estudianteC
        WHERE c.id_canje = :id_canje
    ";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id_canje', $id_canje, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function listarCanjesParaAsistente() {
    $sql = "
        SELECT 
            c.id_canje,
            c.fecha,
            c.estado,
            c.puntos_usados,
            e.cedula,
            e.nombre AS nombre_estudiante,
            e.apellido AS apellido_estudiante,
            SUM(d.cantidad) AS cantidad_total
        FROM tb_canje c
        INNER JOIN tb_detalle_canje d ON c.id_canje = d.id_canjeD
        INNER JOIN tb_estudiantes e ON e.id_estudiante = c.id_estudianteC
        GROUP BY c.id_canje
        ORDER BY c.fecha DESC
    ";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


public function entregarCanje($id_canje) {
    $sql = "UPDATE tb_canje SET estado = 'entregado' WHERE id_canje = :id_canje";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id_canje', $id_canje, PDO::PARAM_INT);
    return $stmt->execute();
}

// Obtener detalles completos de un canje
public function obtenerDetallesCanje($id_canje) {
    // 1. Datos generales del canje y del estudiante
    $sql = "
        SELECT 
            c.id_canje,
            c.fecha,
            c.estado,
            c.puntos_usados,
            e.nombre AS nombre_estudiante,
            e.apellido AS apellido_estudiante,
            e.cedula
        FROM tb_canje c
        INNER JOIN tb_estudiantes e ON e.id_estudiante = c.id_estudianteC
        WHERE c.id_canje = ?
    ";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$id_canje]);
    $canje = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$canje) return null;

    // 2. Detalles de premios
    $sqlDetalles = "
        SELECT 
            p.nombre AS nombre_premio,
            d.cantidad,
            d.puntos
        FROM tb_detalle_canje d
        INNER JOIN tb_premios p ON p.id_premio = d.id_premioD
        WHERE d.id_canjeD = ?
    ";
    $stmt = $this->conn->prepare($sqlDetalles);
    $stmt->execute([$id_canje]);
    $premios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Calcular cantidad total de premios
    $cantidad_total = array_sum(array_column($premios, 'cantidad'));

    $canje['premios'] = $premios;
    $canje['cantidad_total'] = $cantidad_total;

    return $canje;
}


}
?>
