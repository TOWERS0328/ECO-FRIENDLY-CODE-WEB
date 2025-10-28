<?php
class Database {
    private $host = "localhost";
    private $port = "3307";
    private $db_name = "eco_friendly_code";
    private $username = "root";
    private $password = "";
    public $conn;

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die(json_encode(["error" => "Error de conexión: " . $e->getMessage()]));
        }
        return $this->conn;
    }
}
?>
