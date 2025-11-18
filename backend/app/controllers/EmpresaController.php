<?php
require_once __DIR__ . '/../models/Empresa.php';

class EmpresaController {
    private $empresa;

    public function __construct() {
        $this->empresa = new Empresa();
    }

    public function listar() {
        $empresas = $this->empresa->getEmpresas();
        echo json_encode($empresas);
    }
    // 1️⃣ Listar empresas activas
    public function listarActivas() {
        $empresas = $this->empresa->getEmpresasActivas();
        echo json_encode($empresas);
    }

    // 2️⃣ Registrar nueva empresa
    public function registrar() {
        // Obtener datos desde JSON
        $data = json_decode(file_get_contents("php://input"), true);

        $nit = $data['nit'] ?? null;
        $nombre = $data['nombre'] ?? null;
        $logo = $data['logo'] ?? null;
        $contacto = $data['contacto'] ?? null;

        if (!$nit || !$nombre) {
            echo json_encode([
                "status" => "error",
                "message" => "El NIT y nombre son obligatorios"
            ]);
            return;
        }

        $resultado = $this->empresa->registrar($nit, $nombre, $logo, $contacto);
        echo json_encode($resultado);
    }

    // 3️⃣ Actualizar empresa
    public function actualizar() {
        $data = json_decode(file_get_contents("php://input"), true);

        $id_empresa = $data['id_empresa'] ?? null;
        $nit = $data['nit'] ?? null;
        $nombre = $data['nombre'] ?? null;
        $logo = $data['logo'] ?? null;
        $contacto = $data['contacto'] ?? null;
        $estado = $data['estado'] ?? 'activo';

        if (!$id_empresa || !$nit || !$nombre) {
            echo json_encode([
                "status" => "error",
                "message" => "ID, NIT y nombre son obligatorios"
            ]);
            return;
        }

        $resultado = $this->empresa->actualizar($id_empresa, $nit, $nombre, $logo, $contacto, $estado);
        echo json_encode($resultado);
    }

    // 4️⃣ Obtener empresa por ID
    public function obtener() {
        $id_empresa = $_GET['id'] ?? null;
        if (!$id_empresa) {
            echo json_encode([
                "status" => "error",
                "message" => "ID de empresa no proporcionado"
            ]);
            return;
        }

        $empresa = $this->empresa->getEmpresaById($id_empresa);
        if ($empresa) {
            echo json_encode($empresa);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Empresa no encontrada"
            ]);
        }
    }
}
