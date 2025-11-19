<?php
require_once __DIR__ . '/../models/Acopio.php';
require_once __DIR__ . '/../models/Residuo.php';

class AcopioController {

    // Listar canasta de un estudiante (pendiente)
    public function listarCanasta() {
        header("Content-Type: application/json");
        $id_estudiante = $_GET['id_estudiante'] ?? null;
        if (!$id_estudiante) {
            echo json_encode(["status" => "error", "message" => "id_estudiante requerido"]);
            return;
        }

        $acopioModel = new Acopio();
        $acopio = $acopioModel->getPendiente($id_estudiante);

        if (!$acopio) {
            echo json_encode(["status" => "success", "canasta" => [], "puntos_totales" => 0]);
            return;
        }

        $detalles = $acopioModel->getDetalles($acopio['id_acopio']);
        echo json_encode([
            "status" => "success",
            "canasta" => $detalles,
            "puntos_totales" => $acopio['puntos_totales']
        ]);
    }

    // Agregar residuo a la canasta
    public function agregarResiduo() {
        header("Content-Type: application/json");
        $id_estudiante = $_POST['id_estudiante'] ?? null;
        $id_residuo = $_POST['id_residuo'] ?? null;
        $cantidad = $_POST['cantidad'] ?? 1;

        if (!$id_estudiante || !$id_residuo) {
            echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
            return;
        }

        $acopioModel = new Acopio();
        $residuoModel = new Residuo();

        $acopio = $acopioModel->getPendiente($id_estudiante);
        if (!$acopio) {
            $id_acopio = $acopioModel->crearAcopio($id_estudiante);
        } else {
            $id_acopio = $acopio['id_acopio'];
        }

        $residuo = $residuoModel->getById($id_residuo);
        if (!$residuo) {
            echo json_encode(["status" => "error", "message" => "Residuo no encontrado"]);
            return;
        }

        $puntos = $residuo['puntos'] * $cantidad;
        $ok = $acopioModel->agregarDetalle($id_acopio, $id_residuo, $cantidad, $puntos);

        if ($ok) {
            $totalPuntos = $acopioModel->actualizarPuntosTotales($id_acopio);
            echo json_encode([
                "status" => "success",
                "message" => "Residuo agregado",
                "puntos_totales" => $totalPuntos
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al agregar residuo"]);
        }
    }

    // Eliminar residuo de la canasta
    public function eliminarResiduo() {
        header("Content-Type: application/json");
        $id_detalle = $_POST['id_detalle'] ?? null;
        $id_acopio = $_POST['id_acopio'] ?? null;

        if (!$id_detalle || !$id_acopio) {
            echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
            return;
        }

        $acopioModel = new Acopio();
        $ok = $acopioModel->eliminarDetalle($id_detalle);
        if ($ok) {
            $totalPuntos = $acopioModel->actualizarPuntosTotales($id_acopio);
            echo json_encode(["status" => "success", "message" => "Residuo eliminado", "puntos_totales" => $totalPuntos]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al eliminar residuo"]);
        }
    }

    // Finalizar acopio (para que quede pendiente a validar por asistente)
    public function finalizarAcopio() {
        header("Content-Type: application/json");
        $id_acopio = $_POST['id_acopio'] ?? null;
        if (!$id_acopio) {
            echo json_encode(["status" => "error", "message" => "id_acopio requerido"]);
            return;
        }

        $acopioModel = new Acopio();
        $ok = $acopioModel->finalizarAcopio($id_acopio);
        echo json_encode($ok
            ? ["status" => "success", "message" => "Acopio finalizado y pendiente de validación"]
            : ["status" => "error", "message" => "Error al finalizar acopio"]);
    }

    // Validar acopio (solo asistente)
    public function validarAcopio() {
        header("Content-Type: application/json");
        $id_acopio = $_POST['id_acopio'] ?? null;
        if (!$id_acopio) {
            echo json_encode(["status" => "error", "message" => "id_acopio requerido"]);
            return;
        }

        $acopioModel = new Acopio();
        $ok = $acopioModel->validarAcopio($id_acopio);
        echo json_encode($ok
            ? ["status" => "success", "message" => "Acopio validado y puntos asignados al estudiante"]
            : ["status" => "error", "message" => "Error al validar acopio"]);
    }

    // Listar acopios de un estudiante (pendientes o validados)
    public function listarAcopios() {
        header("Content-Type: application/json");
        $id_estudiante = $_GET['id_estudiante'] ?? null;
        $estado = $_GET['estado'] ?? null; // opcional: 'pendiente' o 'validado'

        if (!$id_estudiante) {
            echo json_encode(["status" => "error", "message" => "id_estudiante requerido"]);
            return;
        }

        $acopioModel = new Acopio();
        $acopios = $acopioModel->listarAcopiosPorEstado($id_estudiante, $estado);
        echo json_encode(["status" => "success", "acopios" => $acopios]);
    }
}
?>
