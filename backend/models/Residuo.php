<?php
class Residuo {
    private $conn;
    private $table = "residuos";

    public $id_residuo;
    public $nombre;
    public $descripcion;
    public $puntos;
    public $tipo; // opcional

    public function __construct($db) {
        $this->conn = $db;
    }

    public function crear() {
        // ✅ Se corrige el nombre del parámetro en el INSERT
        $query = "INSERT INTO " . $this->table . " (nombre, descripcion, puntos_otorgados)
                  VALUES (:nombre, :descripcion, :puntos_otorgados)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":puntos_otorgados", $this->puntos);

        if ($stmt->execute()) {
            return true;
        } else {
            // Si hay un error, lo mostramos para depurar
            $error = $stmt->errorInfo();
            error_log("Error al insertar residuo: " . $error[2]);
            return false;
        }
    }

    public function obtenerTodos() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY id_residuo DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>
