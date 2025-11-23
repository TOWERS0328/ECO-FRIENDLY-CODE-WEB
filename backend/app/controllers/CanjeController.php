<?php
require_once __DIR__ . '/../models/Canje.php';

class CanjeController {

    // ================================
    // 1. LISTAR CARRITO
    // ================================
    public function listarCarrito() {
        $input = json_decode(file_get_contents("php://input"), true);
        $id_estudiante = $input["id_estudiante"] ?? $_GET["id_estudiante"] ?? null;

        if (!$id_estudiante) {
            echo json_encode(["status"=>"error","message"=>"ID estudiante requerido"]);
            return;
        }

        $model = new Canje();
        $carrito = $model->listarCarrito($id_estudiante);

        echo json_encode(["status"=>"success","carrito"=>$carrito]);
    }

    // ================================
    // 2. AGREGAR AL CARRITO
    // ================================
    public function agregarCarrito() {
        $data = json_decode(file_get_contents("php://input"), true);
        $id_estudiante = $data["id_estudiante"] ?? null;
        $id_premio = $data["id_premio"] ?? null;
        $cantidad = $data["cantidad"] ?? 1;

        if (!$id_estudiante || !$id_premio) {
            echo json_encode(["status"=>"error","message"=>"Datos incompletos"]);
            return;
        }

        $model = new Canje();
        $res = $model->agregarCarrito($id_estudiante, $id_premio, $cantidad);

        echo json_encode(["status"=>$res ? "success" : "error","message"=>$res ? "Agregado al carrito" : "Error al agregar"]);
    }

    // ================================
    // 3. ACTUALIZAR CANTIDAD
    // ================================
    public function actualizarCantidad() {
        $data = json_decode(file_get_contents("php://input"), true);
        $id_estudiante = $data["id_estudiante"] ?? null;
        $id_premio = $data["id_premio"] ?? null;
        $cantidad = $data["cantidad"] ?? null;

        if (!$id_estudiante || !$id_premio || $cantidad === null) {
            echo json_encode(["status"=>"error","message"=>"Datos incompletos"]);
            return;
        }

        $model = new Canje();
        $res = $model->actualizarCantidad($id_estudiante, $id_premio, $cantidad);

        echo json_encode(["status"=>$res ? "success" : "error","message"=>$res ? "Cantidad actualizada" : "Error al actualizar"]);
    }

    // ================================
    // 4. ELIMINAR ITEM
    // ================================
    public function eliminarItem() {
        $data = json_decode(file_get_contents("php://input"), true);
        $id_estudiante = $data["id_estudiante"] ?? null;
        $id_premio = $data["id_premio"] ?? null;

        if (!$id_estudiante || !$id_premio) {
            echo json_encode(["status"=>"error","message"=>"Datos incompletos"]);
            return;
        }

        $model = new Canje();
        $res = $model->eliminarItem($id_estudiante, $id_premio);

        echo json_encode(["status"=>$res ? "success" : "error","message"=>$res ? "Item eliminado" : "Error al eliminar"]);
    }

    // ================================
    // 5. FINALIZAR CANJE
    // ================================
    public function finalizarCanje() {
        $data = json_decode(file_get_contents("php://input"), true);
        $id_estudiante = $data["id_estudiante"] ?? null;

        if (!$id_estudiante) {
            echo json_encode(["status"=>"error","message"=>"ID estudiante requerido"]);
            return;
        }

        $model = new Canje();
        $res = $model->finalizarCanje($id_estudiante);

        echo json_encode($res);
    }

    // ================================
    // 6. LISTAR HISTORIAL DE UN ESTUDIANTE
    // ================================
    public function listarHistorial() {
        $id_estudiante = $_GET["id_estudiante"] ?? null;

        if (!$id_estudiante) {
            echo json_encode([
                "status" => "error",
                "message" => "ID estudiante requerido"
            ]);
            return;
        }

        $model = new Canje();
        $historial = $model->listarTodosLosCanjes($id_estudiante);

        echo json_encode([
            "status" => "success",
            "historial" => $historial
        ]);
    }

    // ================================
    // 7. LISTAR CANJES PARA ASISTENTE
    // ================================
    public function listarCanjesAsistente() {
        $model = new Canje();
        $canjes = $model->listarCanjesParaAsistente();

        echo json_encode([
            "status" => "success",
            "canjes" => $canjes
        ]);
    }

    // ================================
    // 8. OBTENER DETALLES DE UN CANJE
    // ================================
    public function obtenerCanje() {
        $id_canje = $_GET['id_canje'] ?? null;

        if (!$id_canje) {
            echo json_encode(["status" => "error", "message" => "ID canje requerido"]);
            return;
        }

        $model = new Canje();
        $canje = $model->obtenerCanje($id_canje);
        $premios = $model->obtenerPremiosPorCanje($id_canje);

        echo json_encode([
            "status" => "success",
            "canje" => $canje,
            "premios" => $premios
        ]);
    }

    // ================================
    // 9. ENTREGAR CANJE DESDE ASISTENTE
    // ================================
    public function entregarCanjeAsistente() {
        $data = json_decode(file_get_contents("php://input"), true);
        $id_canje = $data['id_canje'] ?? null;

        if (!$id_canje) {
            echo json_encode(["status" => "error", "message" => "ID canje requerido"]);
            return;
        }

        $model = new Canje();
        $success = $model->entregarCanje($id_canje);

        echo json_encode([
            "status" => $success ? "success" : "error",
            "message" => $success ? "Canje entregado" : "Error al entregar el canje"
        ]);
    }

    public function obtenerDetallesCanje() {
    $id_canje = $_GET['id_canje'] ?? null;
    if (!$id_canje) {
        echo json_encode(["status" => "error", "message" => "ID canje requerido"]);
        return;
    }

    $model = new Canje();
    $detalles = $model->obtenerDetallesCanje($id_canje);

    if (!$detalles) {
        echo json_encode(["status" => "error", "message" => "Canje no encontrado"]);
    } else {
        echo json_encode(["status" => "success", "detalles" => $detalles]);
    }
}


}
?>
