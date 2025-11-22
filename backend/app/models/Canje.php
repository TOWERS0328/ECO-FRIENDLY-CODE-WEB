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
                       p.nombre, p.puntos_requeridos AS puntos_unitarios, p.imagen
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
        $carrito = $this->listarCarrito($id_estudiante);
        if (empty($carrito)) return ["status" => "error", "message" => "El carrito está vacío"];

        $puntosTotales = 0;
        foreach ($carrito as $item) {
            $puntosTotales += $item['puntos_unitarios'] * $item['cantidad'];
        }

        $sql = "SELECT puntos_acumulados FROM tb_estudiantes WHERE id_estudiante = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_estudiante]);
        $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$estudiante) return ["status" => "error", "message" => "Estudiante no encontrado"];
        if ($estudiante["puntos"] < $puntosTotales) return ["status" => "error", "message" => "Puntos insuficientes"];

        $sql = "INSERT INTO tb_canje (id_estudianteC, fecha, estado, puntos_usados) VALUES (?, NOW(), 'pendiente', ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_estudiante, $puntosTotales]);
        $id_canje = $this->conn->lastInsertId();

        foreach ($carrito as $item) {
            $puntosItem = $item['puntos_unitarios'] * $item['cantidad'];
            $sqlDet = "INSERT INTO tb_detalle_canje (id_canjeD, id_premioD, cantidad, puntos) VALUES (?, ?, ?, ?)";
            $stmtDet = $this->conn->prepare($sqlDet);
            $stmtDet->execute([$id_canje, $item['id_premio'], $item['cantidad'], $puntosItem]);
        }

        $sql = "UPDATE tb_estudiantes SET puntos_acumulados = puntos_acumulados - ? WHERE id_estudiante = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$puntosTotales, $id_estudiante]);

        $this->limpiarCarrito($id_estudiante);

        return ["status" => "success", "message" => "Canje realizado correctamente", "id_canje" => $id_canje, "puntos_usados" => $puntosTotales];
    }
}
?>
