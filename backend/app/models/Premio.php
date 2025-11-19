<?php
require_once __DIR__ . '/../config/Database.php';

class Premio
{
    private $conn;
    private $table = "tb_premios";

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // =============================
    // GENERAR CÓDIGO AUTOMÁTICO P###
    // =============================
    private function generarCodigo()
    {
        $sql = "SELECT codigo FROM {$this->table} ORDER BY id_premio DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $last = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$last || empty($last['codigo'])) {
            return "P001";
        }

        $num = intval(substr($last['codigo'], 1)) + 1;
        return "P" . str_pad($num, 3, "0", STR_PAD_LEFT);
    }


    public function getAllPremios()
    {
        $sql = "SELECT 
                    p.id_premio,
                    p.codigo,
                    p.nombre,
                    p.puntos_requeridos,
                    p.stock,
                    p.imagen,
                    p.id_empresaP,
                    e.nombre AS empresa,
                    p.estado
                FROM {$this->table} p
                LEFT JOIN tb_empresas_patrocinadoras e 
                ON p.id_empresaP = e.id_empresa";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ====================================
    // CREAR PREMIO (con código automático)
    // ====================================
    public function crearPremio($nombre, $puntos, $stock, $imagen, $id_empresa)
    {
        $codigo = $this->generarCodigo();

        $sql = "INSERT INTO {$this->table} 
                (codigo, nombre, puntos_requeridos, stock, imagen, id_empresaP) 
                VALUES (:codigo, :nombre, :puntos, :stock, :imagen, :id_empresa)";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':codigo' => $codigo,
            ':nombre' => $nombre,
            ':puntos' => $puntos,
            ':stock' => $stock,
            ':imagen' => $imagen,
            ':id_empresa' => $id_empresa
        ]);
    }

    // Obtener un premio por ID
    public function getById($id_premio)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id_premio = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id_premio]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ====================================
    // ACTUALIZAR PREMIO
    // (sin modificar codigo)
    // ====================================
    public function actualizarPremio($id_premio, $nombre, $puntos, $stock, $imagen, $id_empresa, $estado)
    {
        if ($imagen !== null) {
            $sql = "UPDATE {$this->table} SET 
                        nombre = :nombre,
                        puntos_requeridos = :puntos,
                        stock = :stock,
                        imagen = :imagen,
                        id_empresaP = :id_empresa,
                        estado = :estado
                    WHERE id_premio = :id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':id' => $id_premio,
                ':nombre' => $nombre,
                ':puntos' => $puntos,
                ':stock' => $stock,
                ':imagen' => $imagen,
                ':id_empresa' => $id_empresa,
                ':estado' => $estado
            ]);
        } else {
            $sql = "UPDATE {$this->table} SET 
                        nombre = :nombre,
                        puntos_requeridos = :puntos,
                        stock = :stock,
                        id_empresaP = :id_empresa,
                        estado = :estado
                    WHERE id_premio = :id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':id' => $id_premio,
                ':nombre' => $nombre,
                ':puntos' => $puntos,
                ':stock' => $stock,
                ':id_empresa' => $id_empresa,
                ':estado' => $estado
            ]);
        }
    }

    public function getCatalogoEstudiante()
    {
        $sql = "SELECT 
                id_premio,
                codigo,
                nombre,
                puntos_requeridos,
                stock,
                imagen
            FROM {$this->table}
            WHERE estado = 'activo'
              AND stock > 0";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizarStock($id_premio, $nuevoStock)
    {
        $sql = "UPDATE {$this->table} SET stock = :stock WHERE id_premio = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':stock' => $nuevoStock,
            ':id' => $id_premio
        ]);
    }
}
