<?php
require_once __DIR__ . '/../config/Database.php';

class Estudiante
{
    private $conn;
    private $table = "tb_estudiantes";

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function existeCedula($cedula)
    {
        $sql = "SELECT id_estudiante FROM {$this->table} WHERE cedula = :cedula LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':cedula' => $cedula]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }

    public function crearPerfilEstudiante($id_usuario, $nombre, $apellido, $genero, $cedula, $carrera)
    {

        $sql = "INSERT INTO {$this->table}
                (id_usuarioE, nombre, apellido, genero, cedula, carrera)
                VALUES
                (:id_usuarioE, :nombre, :apellido, :genero, :cedula, :carrera)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':id_usuarioE' => $id_usuario,
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':genero' => $genero,
            ':cedula' => $cedula,
            ':carrera' => $carrera
        ]);
    }
    public function getPerfil($id_usuario)
    {
        $sql = "SELECT * FROM tb_estudiantes WHERE id_usuarioE = :id_usuario LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_usuario' => $id_usuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllEstudiantes()
    {
        $sql = "SELECT e.id_estudiante, e.nombre, e.apellido, e.genero, e.cedula, e.carrera, e.puntos_acumulados, u.correo
            FROM tb_estudiantes e
            JOIN tb_usuarios u ON u.id_usuario = e.id_usuarioE
            WHERE u.rol = 'estudiante'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


public function getByIdEstudiante($id_estudiante)
    {
        $sql = "SELECT e.id_estudiante, e.id_usuarioE, e.nombre, e.apellido, e.genero, e.cedula, e.carrera, e.puntos_acumulados, e.foto_perfil, u.correo
                FROM tb_estudiantes e
                LEFT JOIN tb_usuarios u ON u.id_usuario = e.id_usuarioE
                WHERE e.id_estudiante = :id_estudiante
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_estudiante' => $id_estudiante]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar los datos del perfil del estudiante (tabla tb_estudiantes)
    public function actualizarPerfilEstudiante($id_estudiante, $nombre, $apellido, $genero, $cedula, $carrera)
    {
        $sql = "UPDATE {$this->table}
                SET nombre = :nombre,
                    apellido = :apellido,
                    genero = :genero,
                    cedula = :cedula,
                    carrera = :carrera
                WHERE id_estudiante = :id_estudiante";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id_estudiante' => $id_estudiante,
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':genero' => $genero,
            ':cedula' => $cedula,
            ':carrera' => $carrera
        ]);
    }

    // Actualizar correo en la tabla tb_usuarios
    public function actualizarCorreoUsuario($id_usuario, $nuevo_correo)
    {
        $sql = "UPDATE tb_usuarios SET correo = :correo WHERE id_usuario = :id_usuario";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':correo' => $nuevo_correo,
            ':id_usuario' => $id_usuario
        ]);
    }

    // Actualizar password (hash) en tb_usuarios
    public function actualizarPasswordUsuario($id_usuario, $passwordHash)
    {
        $sql = "UPDATE tb_usuarios SET password = :password WHERE id_usuario = :id_usuario";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':password' => $passwordHash,
            ':id_usuario' => $id_usuario
        ]);
    }

    public function cedulaExisteEnOtro($cedula, $id_estudiante)
{
    $sql = "SELECT id_estudiante FROM {$this->table} 
            WHERE cedula = :cedula 
            AND id_estudiante != :id_estudiante
            LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([
        ':cedula' => $cedula,
        ':id_estudiante' => $id_estudiante
    ]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
}

public function correoExisteEnOtro($correo, $id_usuario)
{
    $sql = "SELECT id_usuario FROM tb_usuarios 
            WHERE correo = :correo 
            AND id_usuario != :id_usuario
            LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([
        ':correo' => $correo,
        ':id_usuario' => $id_usuario
    ]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
}


}
