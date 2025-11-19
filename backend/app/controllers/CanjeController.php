<?php
require_once __DIR__ . '/../models/Canje.php';
require_once __DIR__ . '/../models/Estudiante.php';
require_once __DIR__ . '/../models/Premio.php';

class CanjeController {

    // Listar carrito de premios
    public function listarCarrito() {
        header("Content-Type: application/json");

        $id_estudiante = $_GET['id_estudiante'] ?? null;
        if (!$id_estudiante) {
            echo json_encode(["status" => "error", "message" => "id_estudiante requerido"]);
            return;
        }

        $canjeModel = new Canje();
        $estudianteModel = new Estudiante();

        $canje = $canjeModel->getPendientePorEstudiante($id_estudiante);

        if (!$canje) {
            echo json_encode([
                "status" => "success",
                "carrito" => [],
                "puntos_totales" => $estudianteModel->getPuntos($id_estudiante)
            ]);
            return;
        }

        $detalles = $canjeModel->getDetalles($canje['id_canje']);
        echo json_encode([
            "status" => "success",
            "carrito" => $detalles,
            "puntos_totales" => $estudianteModel->getPuntos($id_estudiante)
        ]);
    }

    // Agregar premio al carrito
    public function agregarPremio() {
        header("Content-Type: application/json");

        $id_estudiante = $_POST['id_estudiante'] ?? null;
        $id_premio = $_POST['id_premio'] ?? null;
        $cantidad = $_POST['cantidad'] ?? 1;

        if (!$id_estudiante || !$id_premio) {
            echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
            return;
        }

        $canjeModel = new Canje();
        $premioModel = new Premio();
        $estudianteModel = new Estudiante();

        $premio = $premioModel->getById($id_premio);
        if (!$premio) {
            echo json_encode(["status" => "error", "message" => "Premio no encontrado"]);
            return;
        }

        $puntos_necesarios = $premio['puntos_requeridos'] * $cantidad;
        $puntos_estudiante = $estudianteModel->getPuntos($id_estudiante);

        if ($premio['stock'] < $cantidad) {
            echo json_encode(["status" => "error", "message" => "No hay stock suficiente"]);
            return;
        }

        if ($puntos_estudiante < $puntos_necesarios) {
            echo json_encode(["status" => "error", "message" => "No tienes suficientes puntos"]);
            return;
        }

        $canje = $canjeModel->getPendientePorEstudiante($id_estudiante);
        $id_canje = $canje ? $canje['id_canje'] : $canjeModel->crearCanje($id_estudiante, 0);

        $ok = $canjeModel->agregarDetalle($id_canje, $id_premio, $cantidad, $puntos_necesarios);

        echo json_encode($ok
            ? ["status" => "success", "message" => "Premio agregado al carrito"]
            : ["status" => "error", "message" => "Error al agregar premio"]);
    }

    // Confirmar canje
    public function confirmarCanje() {
        header("Content-Type: application/json");

        $id_estudiante = $_POST['id_estudiante'] ?? null;
        if (!$id_estudiante) {
            echo json_encode(["status" => "error", "message" => "id_estudiante requerido"]);
            return;
        }

        $canjeModel = new Canje();
        $premioModel = new Premio();
        $estudianteModel = new Estudiante();

        $canje = $canjeModel->getPendientePorEstudiante($id_estudiante);
        if (!$canje) {
            echo json_encode(["status" => "error", "message" => "No hay canje pendiente"]);
            return;
        }

        $detalles = $canjeModel->getDetalles($canje['id_canje']);
        $total_puntos = 0;

        foreach ($detalles as $detalle) {
            $total_puntos += $detalle['puntos'];
            $premioActual = $premioModel->getById($detalle['id_premioD']);
            $premioModel->actualizarStock($detalle['id_premioD'], $premioActual['stock'] - $detalle['cantidad']);
        }

        $estudianteModel->restarPuntos($id_estudiante, $total_puntos);
        $canjeModel->actualizarPuntosUsados($canje['id_canje'], $total_puntos);

        echo json_encode(["status" => "success", "message" => "Canje confirmado", "puntos_usados" => $total_puntos]);
    }

    // Marcar canje como entregado
    public function entregarCanje() {
        header("Content-Type: application/json");
        $id_canje = $_POST['id_canje'] ?? null;
        if (!$id_canje) {
            echo json_encode(["status" => "error", "message" => "id_canje requerido"]);
            return;
        }

        $canjeModel = new Canje();
        $ok = $canjeModel->actualizarEstado($id_canje, 'entregado');

        echo json_encode($ok
            ? ["status" => "success", "message" => "Canje entregado"]
            : ["status" => "error", "message" => "Error al actualizar estado"]);
    }
}
?>
