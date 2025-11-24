<?php
require_once __DIR__ . '/../config/Database.php';

class Dashboard {
    private $conn;

    public function __construct() {
        $this->conn = (new Database())->getConnection();
    }

    public function totalEstudiantes() {
        try {
            $sql = "SELECT COUNT(*) AS total_estudiantes FROM tb_estudiantes";
            $stmt = $this->conn->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_estudiantes' => 0];
        } catch (Exception $e) {
            return ['total_estudiantes' => 0];
        }
    }

    public function totalReciclaje() {
        try {
            $sql = "SELECT SUM(dc.cantidad) AS total_reciclaje
                    FROM tb_detalle_acopio dc
                    JOIN tb_acopio a ON dc.id_acopioD = a.id_acopio
                    WHERE a.estado = 'validado'";
            $stmt = $this->conn->query($sql);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return ['total_reciclaje' => $res['total_reciclaje'] ?? 0];
        } catch (Exception $e) {
            return ['total_reciclaje' => 0];
        }
    }

    public function totalPremiosEntregados() {
        try {
            $sql = "SELECT SUM(dc.cantidad) AS total_premios_entregados
                    FROM tb_detalle_canje dc
                    JOIN tb_canje c ON dc.id_canjeD = c.id_canje
                    WHERE c.estado = 'entregado'";
            $stmt = $this->conn->query($sql);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return ['total_premios_entregados' => $res['total_premios_entregados'] ?? 0];
        } catch (Exception $e) {
            return ['total_premios_entregados' => 0];
        }
    }

    public function topCarreras($limit = 3) {
        try {
            $sql = "SELECT e.carrera, SUM(dc.cantidad) AS total_reciclaje
                    FROM tb_estudiantes e
                    JOIN tb_acopio a ON e.id_estudiante = a.id_estudianteA
                    JOIN tb_detalle_acopio dc ON a.id_acopio = dc.id_acopioD
                    WHERE a.estado = 'validado'
                    GROUP BY e.carrera
                    ORDER BY total_reciclaje DESC
                    LIMIT :limit";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function topEstudiantes($limit = 3) {
        try {
            $sql = "SELECT e.nombre, e.apellido, SUM(dc.cantidad) AS total_reciclaje
                    FROM tb_estudiantes e
                    JOIN tb_acopio a ON e.id_estudiante = a.id_estudianteA
                    JOIN tb_detalle_acopio dc ON a.id_acopio = dc.id_acopioD
                    WHERE a.estado = 'validado'
                    GROUP BY e.id_estudiante
                    ORDER BY total_reciclaje DESC
                    LIMIT :limit";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }
}
