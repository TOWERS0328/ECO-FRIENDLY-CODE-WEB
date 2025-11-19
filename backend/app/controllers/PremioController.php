<?php
require_once __DIR__ . '/../models/Empresa.php';
require_once __DIR__ . '/../models/Premio.php';

class PremioController {

    public function listar() {
        header("Content-Type: application/json");
        $premioModel = new Premio();
        echo json_encode($premioModel->getAllPremios());
    }

    public function crear() {
        header("Content-Type: application/json");

        $nombre = $_POST['nombre'] ?? null;
        $puntos = $_POST['puntos_requeridos'] ?? null;
        $stock = $_POST['stock'] ?? 0;
        $id_empresa = $_POST['id_empresaP'] ?? null;

        if (!$nombre || !$puntos || !$id_empresa) {
            echo json_encode([
                "status" => "error",
                "message" => "Campos obligatorios faltantes"
            ]);
            return;
        }

        // Imagen
        $imagenPath = null;
        if (!empty($_FILES['imagen']['name'])) {
            $uploadDir = __DIR__ . '/../../uploads/premios/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $filename = time() . "_" . bin2hex(random_bytes(6)) . "." . $ext;
            $dest = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $dest)) {
                $imagenPath = "uploads/premios/" . $filename;
            }
        }

        $premioModel = new Premio();
        $ok = $premioModel->crearPremio($nombre, $puntos, $stock, $imagenPath, $id_empresa);

        echo json_encode($ok ?
            ["status" => "success", "message" => "Premio registrado"] :
            ["status" => "error", "message" => "Error al registrar"]
        );
    }

    public function actualizar() {
        header("Content-Type: application/json");

        $id = $_POST['id_premio'] ?? null;
        if (!$id) {
            echo json_encode(["status" => "error", "message" => "id_premio requerido"]);
            return;
        }

        $premioModel = new Premio();
        $premio = $premioModel->getById($id);

        if (!$premio) {
            echo json_encode(["status" => "error", "message" => "Premio no encontrado"]);
            return;
        }

        $nombre = $_POST['nombre'] ?? $premio['nombre'];
        $puntos = $_POST['puntos_requeridos'] ?? $premio['puntos_requeridos'];
        $stock = $_POST['stock'] ?? $premio['stock'];
        $id_empresa = $_POST['id_empresaP'] ?? $premio['id_empresaP'];
        $estado = $_POST['estado'] ?? $premio['estado'];

        // Imagen opcional
        $imagenPath = null;
        if (!empty($_FILES['imagen']['name'])) {
            $uploadDir = __DIR__ . '/../../uploads/premios/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $filename = time() . "_" . bin2hex(random_bytes(6)) . "." . $ext;
            $dest = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $dest)) {
                $imagenPath = "uploads/premios/" . $filename;
            }
        }

        $ok = $premioModel->actualizarPremio($id, $nombre, $puntos, $stock, $imagenPath, $id_empresa, $estado);

        echo json_encode($ok ?
            ["status" => "success", "message" => "Premio actualizado"] :
            ["status" => "error", "message" => "Error al actualizar"]
        );
    }

    public function listarEmpresas() {
        header("Content-Type: application/json");
        $empresaModel = new Empresa();
        echo json_encode($empresaModel->getEmpresasActivas());
    }

    public function catalogo() {
    header("Content-Type: application/json");

    $premioModel = new Premio();
    $data = $premioModel->getCatalogoEstudiante();

    echo json_encode($data);
}

}
