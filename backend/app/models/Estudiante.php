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

    // 🔹 Obtener puntos
    public function getPuntos($id_estudiante)
    {
        $sql = "SELECT puntos_acumulados FROM {$this->table} WHERE id_estudiante = :id_estudiante LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_estudiante' => $id_estudiante]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['puntos_acumulados'] ?? 0;
    }

    // 🔹 Sumar puntos
    public function sumarPuntos($id_estudiante, $puntos)
    {
        $sql = "UPDATE {$this->table} SET puntos_acumulados = puntos_acumulados + :puntos WHERE id_estudiante = :id_estudiante";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':puntos' => $puntos,
            ':id_estudiante' => $id_estudiante
        ]);
    }

    // 🔹 Restar puntos
    public function restarPuntos($id_estudiante, $puntos)
    {
        $sql = "UPDATE {$this->table} SET puntos_acumulados = puntos_acumulados - :puntos WHERE id_estudiante = :id_estudiante";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':puntos' => $puntos,
            ':id_estudiante' => $id_estudiante
        ]);
    }

    // 🔹 Validar cédula
    public function existeCedula($cedula)
    {
        $sql = "SELECT id_estudiante FROM {$this->table} WHERE cedula = :cedula LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':cedula' => $cedula]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }

    // 🔹 Crear perfil estudiante con foto por defecto
    public function crearPerfilEstudiante($id_usuario, $nombre, $apellido, $genero, $cedula, $carrera)
    {
        $sql = "INSERT INTO {$this->table}
                (id_usuarioE, nombre, apellido, genero, cedula, carrera, foto_perfil)
                VALUES
                (:id_usuarioE, :nombre, :apellido, :genero, :cedula, :carrera, :foto_perfil)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id_usuarioE' => $id_usuario,
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':genero' => $genero,
            ':cedula' => $cedula,
            ':carrera' => $carrera,
            ':foto_perfil' => "uploads/estudiantes/default.jpg"
        ]);
    }

    // 🔹 Obtener perfil por id_usuario
    public function getPerfil($id_usuario)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id_usuarioE = :id_usuario LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_usuario' => $id_usuario]);
        $perfil = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($perfil && empty($perfil['foto_perfil'])) {
            $perfil['foto_perfil'] = "uploads/estudiantes/default.jpg";
        }

        return $perfil;
    }

    public function getPerfilS($id_usuario) {
        $sql = "SELECT e.id_estudiante, e.nombre, e.apellido, e.genero, e.cedula, e.carrera, 
                       e.puntos_acumulados, e.foto_perfil, u.correo
                FROM {$this->table} e
                JOIN tb_usuarios u ON u.id_usuario = e.id_usuarioE
                WHERE e.id_usuarioE = :id_usuario
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_usuario' => $id_usuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 🔹 Obtener todos los estudiantes
    public function getAllEstudiantes()
    {
        $sql = "SELECT e.id_estudiante, e.nombre, e.apellido, e.genero, e.cedula, e.carrera, e.puntos_acumulados, e.foto_perfil, u.correo
                FROM tb_estudiantes e
                JOIN tb_usuarios u ON u.id_usuario = e.id_usuarioE
                WHERE u.rol = 'estudiante'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Devuelve todos los estudiantes incluyendo la contraseña hasheada (hash)
public function getAllEstudiantesConPassword()
{
    $sql = "SELECT e.id_estudiante, e.nombre, e.apellido, e.genero, e.cedula, e.carrera, e.puntos_acumulados, e.foto_perfil, u.correo, u.password
            FROM tb_estudiantes e
            JOIN tb_usuarios u ON u.id_usuario = e.id_usuarioE
            WHERE u.rol = 'estudiante'";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



    // 🔹 Obtener por id_estudiante
    public function getByIdEstudiante($id_estudiante)
    {
        $sql = "SELECT e.id_estudiante, e.id_usuarioE, e.nombre, e.apellido, e.genero, e.cedula, e.carrera, e.puntos_acumulados, e.foto_perfil, u.correo
                FROM tb_estudiantes e
                LEFT JOIN tb_usuarios u ON u.id_usuario = e.id_usuarioE
                WHERE e.id_estudiante = :id_estudiante
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_estudiante' => $id_estudiante]);
        $perfil = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($perfil && empty($perfil['foto_perfil'])) {
            $perfil['foto_perfil'] = "uploads/estudiantes/default.jpg";
        }

        return $perfil;
    }

    // 🔹 Actualizar perfil (sin foto)
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

    // 🔹 Actualizar correo en tb_usuarios
    public function actualizarCorreoUsuario($id_usuario, $nuevo_correo)
    {
        $sql = "UPDATE tb_usuarios SET correo = :correo WHERE id_usuario = :id_usuario";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':correo' => $nuevo_correo,
            ':id_usuario' => $id_usuario
        ]);
    }

    // 🔹 Actualizar password en tb_usuarios
    public function actualizarPasswordUsuario($id_usuario, $passwordHash)
    {
        $sql = "UPDATE tb_usuarios SET password = :password WHERE id_usuario = :id_usuario";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':password' => $passwordHash,
            ':id_usuario' => $id_usuario
        ]);
    }

    // 🔹 Validar cédula en otro estudiante
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

    // 🔹 Validar correo en otro usuario
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

    // 🔹 Actualizar foto de perfil
    public function actualizarFotoPerfil($id_estudiante, $fotoPath)
    {
        $sql = "UPDATE {$this->table} SET foto_perfil = :foto WHERE id_estudiante = :id_estudiante";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':foto' => $fotoPath,
            ':id_estudiante' => $id_estudiante
        ]);
    }
}
?>
