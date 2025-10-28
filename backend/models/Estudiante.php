<?php
class Estudiante {
    private $conn;
    private $table = "estudiantes";

    public $id_estudiantil;
    public $nombre;
    public $apellido;
    public $genero;
    public $carrera;
    public $email;
    public $password;

    public function __construct($db) {
        $this->conn = $db;
    }

    // 🔹 Verificar si el email ya existe
    public function verificarEmail() {
        $query = "SELECT email FROM " . $this->table . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();
        return $stmt;
    }

    // 🔹 Registrar estudiante
    public function registrar() {
        $query = "INSERT INTO " . $this->table . " 
                  (id_estudiantil, nombre, apellido, genero, carrera, email, password)
                  VALUES (:id_estudiantil, :nombre, :apellido, :genero, :carrera, :email, :password)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id_estudiantil", $this->id_estudiantil);
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":apellido", $this->apellido);
        $stmt->bindParam(":genero", $this->genero);
        $stmt->bindParam(":carrera", $this->carrera);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);

        return $stmt->execute();
    }

    // 🔹 Login (buscar por email)
    public function login() {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();
        return $stmt;
    }
}
?>
