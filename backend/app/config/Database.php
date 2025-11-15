<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        // Cargar variables del archivo .env
        $envPath = __DIR__ . '/../../.env';
        if (file_exists($envPath)) {
            $env = parse_ini_file($envPath);
            $this->host = $env['DB_HOST'];
            $this->db_name = $env['DB_NAME'];
            $this->username = $env['DB_USER'];
            $this->password = $env['DB_PASS'];
        } else {
            die("⚠️ No se encontró el archivo .env");
        }
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name}",
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            echo "❌ Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>
