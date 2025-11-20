<?php
require_once __DIR__ . '/../models/Acopio.php';

class AcopioController {

    // ================================
    // 1. LISTAR CANASTA
    // ================================
    public function listarCanasta() {

        // Aceptar JSON o GET
        $input = json_decode(file_get_contents("php://input"), true);
        if ($input !== null && isset($input["id_estudiante"])) {
            $id_estudiante = $input["id_estudiante"];
        } else {
            $id_estudiante = $_GET["id_estudiante"] ?? null;
        }

        if (!$id_estudiante) {
            echo json_encode([
                "status" => "error",
                "message" => "ID estudiante requerido"
            ]);
            return;
        }

        $model = new Acopio();
        $canasta = $model->listarCanasta($id_estudiante);

        echo json_encode([
            "status" => "success",
            "canasta" => $canasta
        ]);
    }

    // ================================
    // 2. AGREGAR A CANASTA
    // ================================
    public function agregarCanasta() {

        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data) {
            echo json_encode(["status" => "error", "message" => "JSON inválido"]);
            return;
        }

        $id_estudiante = $data["id_estudiante"] ?? null;
        $id_residuo = $data["id_residuo"] ?? null;
        $cantidad = $data["cantidad"] ?? 1;

        if (!$id_estudiante || !$id_residuo) {
            echo json_encode([
                "status" => "error",
                "message" => "Datos incompletos"
            ]);
            return;
        }

        $model = new Acopio();
        $res = $model->agregarCanasta($id_estudiante, $id_residuo, $cantidad);

        echo json_encode([
            "status" => $res ? "success" : "error",
            "message" => $res ? "Agregado a la canasta" : "Error al agregar"
        ]);
    }

    // ================================
    // 3. FINALIZAR ACOPIO
    // ================================
    public function finalizarAcopio() {

        // Aceptar JSON también aquí
        $data = json_decode(file_get_contents("php://input"), true);

        if ($data !== null && isset($data["id_estudiante"])) {
            $id_estudiante = $data["id_estudiante"];
        } else {
            $id_estudiante = $_POST["id_estudiante"] ?? null;
        }

        if (!$id_estudiante) {
            echo json_encode([
                "status" => "error",
                "message" => "ID estudiante requerido"
            ]);
            return;
        }

        $model = new Acopio();
        $canasta = $model->listarCanasta($id_estudiante);

        if (empty($canasta)) {
            echo json_encode(["status" => "error", "message" => "La canasta está vacía"]);
            return;
        }

        $puntosTotales = 0;

        // Calcular puntos
        foreach ($canasta as $item) {
            $pUnit = $model->obtenerPuntosResiduo($item["id_residuo"]);
            $puntosTotales += $pUnit * $item["cantidad"];
        }

        // Crear acopio real
        $id_acopio = $model->crearAcopio($id_estudiante, $puntosTotales);

        // Insertar detalles
        foreach ($canasta as $item) {
            $pUnit = $model->obtenerPuntosResiduo($item["id_residuo"]);
            $puntosItem = $pUnit * $item["cantidad"];
            $model->insertarDetalle($id_acopio, $item["id_residuo"], $item["cantidad"], $puntosItem);
        }

        // Limpiar canasta temporal
        $model->limpiarCanasta($id_estudiante);

        echo json_encode([
            "status" => "success",
            "message" => "Acopio creado correctamente",
            "id_acopio" => $id_acopio,
            "puntos_totales" => $puntosTotales
        ]);
    }

    public function actualizarCantidad() {

    $data = json_decode(file_get_contents("php://input"), true);

    $id_estudiante = $data["id_estudiante"] ?? null;
    $id_residuo = $data["id_residuo"] ?? null;
    $cantidad = $data["cantidad"] ?? null;

    if (!$id_estudiante || !$id_residuo || $cantidad === null) {
        echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
        return;
    }

    $model = new Acopio();
    $res = $model->actualizarCantidad($id_estudiante, $id_residuo, $cantidad);

    echo json_encode([
        "status" => $res ? "success" : "error",
        "message" => $res ? "Cantidad actualizada" : "Error al actualizar"
    ]);
}

public function actualizarEstado() {
    $data = json_decode(file_get_contents("php://input"), true);
    $id_acopio = $data['id_acopio'] ?? null;
    $nuevo_estado = $data['estado'] ?? null;

    if (!$id_acopio || !$nuevo_estado) {
        echo json_encode(["status" => "error", "message" => "ID de acopio y estado requeridos"]);
        return;
    }

    $model = new Acopio();
    $res = $model->actualizarEstado($id_acopio, $nuevo_estado);

    echo json_encode([
        "status" => $res ? "success" : "error",
        "message" => $res ? "Estado actualizado correctamente" : "No se pudo actualizar el estado"
    ]);
}


public function eliminarItem() {

    $data = json_decode(file_get_contents("php://input"), true);

    $id_estudiante = $data["id_estudiante"] ?? null;
    $id_residuo = $data["id_residuo"] ?? null;

    if (!$id_estudiante || !$id_residuo) {
        echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
        return;
    }

    $model = new Acopio();
    $res = $model->eliminarItem($id_estudiante, $id_residuo);

    echo json_encode([
        "status" => $res ? "success" : "error",
        "message" => $res ? "Item eliminado" : "Error al eliminar"
    ]);
}

}
