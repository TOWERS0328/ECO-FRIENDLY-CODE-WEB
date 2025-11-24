<?php
require_once __DIR__ . '/../models/Dashboard.php';

class DashboardController {
    private $dashboard;

    public function __construct() {
        $this->dashboard = new Dashboard();
    }

    public function getEstadisticas() {
        try {
            $data = [
                'status' => 'success',
                'total_estudiantes' => $this->dashboard->totalEstudiantes()['total_estudiantes'],
                'total_reciclaje' => $this->dashboard->totalReciclaje()['total_reciclaje'],
                'total_premios_entregados' => $this->dashboard->totalPremiosEntregados()['total_premios_entregados'],
                'top_carreras' => $this->dashboard->topCarreras(),
                'top_estudiantes' => $this->dashboard->topEstudiantes()
            ];
        } catch (Exception $e) {
            $data = [
                'status' => 'error',
                'message' => $e->getMessage(),
                'total_estudiantes' => 0,
                'total_reciclaje' => 0,
                'total_premios_entregados' => 0,
                'top_carreras' => [],
                'top_estudiantes' => []
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
