<?php
require_once __DIR__ . '/../models/Residuo.php';

class ResiduoController {

    // Listar residuos
    public function listar() {
        header("Content-Type: application/json");
        $residuoModel = new Residuo();
        echo json_encode($residuoModel->getAllResiduos());
    }

    // Crear residuo
    public function crear() {
        header("Content-Type: application/json");

        $nombre = $_POST['nombre'] ?? null;
        $tipo = $_POST['tipo'] ?? null;
        $puntos = $_POST['puntos'] ?? null;
        $estado = $_POST['estado'] ?? "Activo";

        if (!$nombre || !$tipo || !$puntos) {
            echo json_encode(["status" => "error", "message" => "Campos obligatorios faltantes"]);
            return;
        }

        // Imagen
        $imagenPath = null;
        if (!empty($_FILES['imagen']['name'])) {
            $uploadDir = __DIR__ . '/../../uploads/residuos/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $filename = time() . "_" . bin2hex(random_bytes(6)) . "." . $ext;
            $dest = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $dest)) {
                $imagenPath = "uploads/residuos/" . $filename;
            }
        }

        $residuoModel = new Residuo();
        $ok = $residuoModel->crearResiduo($nombre, $tipo, $puntos, $imagenPath, $estado);

        echo json_encode($ok
            ? ["status" => "success", "message" => "Residuo registrado"]
            : ["status" => "error", "message" => "Error al registrar"]);
    }

    // Actualizar residuo
    public function actualizar() {
        header("Content-Type: application/json");

        $id = $_POST['id_residuo'] ?? null;
        if (!$id) {
            echo json_encode(["status" => "error", "message" => "id_residuo requerido"]);
            return;
        }

        $residuoModel = new Residuo();
        $residuo = $residuoModel->getById($id);

        if (!$residuo) {
            echo json_encode(["status" => "error", "message" => "Residuo no encontrado"]);
            return;
        }

        $nombre = $_POST['nombre'] ?? $residuo['nombre'];
        $tipo = $_POST['tipo'] ?? $residuo['tipo'];
        $puntos = $_POST['puntos'] ?? $residuo['puntos'];
        $estado = $_POST['estado'] ?? $residuo['estado'];

        // Imagen nueva (opcional)
        $imagenPath = null;
        if (!empty($_FILES['imagen']['name'])) {
            $uploadDir = __DIR__ . '/../../uploads/residuos/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $filename = time() . "_" . bin2hex(random_bytes(6)) . "." . $ext;
            $dest = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $dest)) {
                $imagenPath = "uploads/residuos/" . $filename;
            }
        }

        $ok = $residuoModel->actualizarResiduo($id, $nombre, $tipo, $puntos, $imagenPath, $estado);

        echo json_encode($ok
            ? ["status" => "success", "message" => "Residuo actualizado"]
            : ["status" => "error", "message" => "Error al actualizar"]);
    }

    // Obtener residuo por id
    public function obtener() {
        header("Content-Type: application/json");
        $id = $_GET['id_residuo'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "id_residuo requerido"]);
            return;
        }

        $residuoModel = new Residuo();
        $residuo = $residuoModel->getById($id);

        if ($residuo) {
            echo json_encode($residuo);
        } else {
            echo json_encode(["status" => "error", "message" => "Residuo no encontrado"]);
        }
    }

    public function listarActivos() {
    header("Content-Type: application/json");
    $residuoModel = new Residuo();
    echo json_encode($residuoModel->getResiduosActivos());
}


}
?>
