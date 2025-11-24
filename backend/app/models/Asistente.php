<?php
require_once __DIR__ . '/../config/Database.php';

class Asistente
{
    private $conn;
    private $table = "tb_asistente";

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

   public function getPerfil($id_usuario) {
        $sql = "SELECT a.id_asistente, a.nombre, a.apellido, a.genero, a.cedula, a.area,a.foto_perfil, u.correo
                FROM {$this->table} a
                JOIN tb_usuarios u ON u.id_usuario = a.id_usuarioA
                WHERE a.id_usuarioA = :id_usuario
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_usuario' => $id_usuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // 🔹 Validar cédula
    public function existeCedula($cedula)
    {
        $sql = "SELECT id_asistente FROM {$this->table} WHERE cedula = :cedula LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':cedula' => $cedula]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }

    // 🔹 Validar correo en otro usuario
    public function correoExisteEnOtro($correo, $id_usuario)
    {
        $sql = "SELECT id_usuario FROM tb_usuarios WHERE correo = :correo AND id_usuario != :id_usuario LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':correo' => $correo,
            ':id_usuario' => $id_usuario
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }

    // 🔹 Crear asistente
    public function crearAsistente($id_usuario, $cedula, $nombre, $apellido, $genero, $area)
    {
        $sql = "INSERT INTO {$this->table}
                (id_usuarioA, nombre, apellido, genero, cedula, foto_perfil)
                VALUES
                (:id_usuarioA, :nombre, :apellido, :genero, :cedula, :foto)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id_usuarioA' => $id_usuario,
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':genero' => $genero,
            ':cedula' => $cedula,
            ':foto' => "uploads/asistentes/default.jpg"
        ]);
    }
    public function actualizarFotoPerfil($id_asistente, $fotoPath)
    {
        $sql = "UPDATE {$this->table} SET foto_perfil = :foto WHERE id_asistente = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':foto' => $fotoPath,
            ':id' => $id_asistente
        ]);
    }

    // 🔹 Obtener todos los asistentes
    public function getAllAsistentes()
    {
        $sql = "SELECT a.id_asistente, a.cedula, a.nombre, a.apellido, a.genero, a.area, u.correo
                FROM {$this->table} a
                JOIN tb_usuarios u ON u.id_usuario = a.id_usuarioA
                WHERE u.rol = 'asistente'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔹 Obtener por id
    public function getById($id_asistente)
    {
        $sql = "SELECT a.id_asistente, a.id_usuarioA, a.cedula, a.nombre, a.apellido, a.genero, a.area, u.correo
                FROM {$this->table} a
                LEFT JOIN tb_usuarios u ON u.id_usuario = a.id_usuarioA
                WHERE a.id_asistente = :id_asistente
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_asistente' => $id_asistente]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 🔹 Actualizar asistente
    public function actualizarAsistente($id_asistente, $cedula, $nombre, $apellido, $genero, $area)
    {
        $sql = "UPDATE {$this->table}
                SET cedula = :cedula,
                    nombre = :nombre,
                    apellido = :apellido,
                    genero = :genero,
                    area = :area
                WHERE id_asistente = :id_asistente";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id_asistente' => $id_asistente,
            ':cedula' => $cedula,
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':genero' => $genero,
            ':area' => $area
        ]);
    }

    // 🔹 Actualizar correo usuario
    public function actualizarCorreoUsuario($id_usuario, $correo)
    {
        $sql = "UPDATE tb_usuarios SET correo = :correo WHERE id_usuario = :id_usuario";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':correo' => $correo,
            ':id_usuario' => $id_usuario
        ]);
    }

    // 🔹 Actualizar password usuario
    public function actualizarPasswordUsuario($id_usuario, $hash)
    {
        $sql = "UPDATE tb_usuarios SET password = :password WHERE id_usuario = :id_usuario";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':password' => $hash,
            ':id_usuario' => $id_usuario
        ]);
    }

    public function getByIdAll($id_asistente)
{
    $sql = "SELECT a.id_asistente, a.id_usuarioA, a.cedula, a.nombre, a.apellido, a.genero, a.area, a.foto_perfil, u.correo
            FROM {$this->table} a
            LEFT JOIN tb_usuarios u ON u.id_usuario = a.id_usuarioA
            WHERE a.id_asistente = :id_asistente
            LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([':id_asistente' => $id_asistente]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

}
?>
