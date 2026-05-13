<?php
/**
 * [SECURITY FIX] 2026-05-13 - PROBLEMA-01
 *   Contraseña eliminada del WHERE de la query SQL.
 *   password_verify() con compatibilidad de transición.
 */
include_once 'Conexion.php';

class LoginAsistente {
    protected $objetos;
    protected $acceso;

    public function __construct() {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }

    public function Loguearse(string $cedula, string $pass): array {
        $sql = "SELECT la.*, ra.nombre_asistente, ra.apellido_asistente,
                       ra.asistente_tipo, tp.nombre_tipo
                FROM login_asistente la
                INNER JOIN registro_asistente ra ON la.id_asistente = ra.id_asistente
                INNER JOIN tipo_paciente tp ON ra.asistente_tipo = tp.id_tipo_us
                WHERE ra.cedula_asistente = :cedula AND la.status = 'activo'";
        $query = $this->acceso->prepare($sql);
        $query->execute([':cedula' => $cedula]);
        $usuario = $query->fetch();

        if (!$usuario) { $this->objetos = []; return []; }

        $hash = $usuario->password_hash;
        $info = password_get_info($hash);

        if ($info['algo'] === null || $info['algo'] === 0) {
            if ($pass !== $hash) { $this->objetos = []; return []; }
            $this->_actualizarHash($usuario->id_asistente, password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]));
        } else {
            if (!password_verify($pass, $hash)) { $this->objetos = []; return []; }
            if (password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12])) {
                $this->_actualizarHash($usuario->id_asistente, password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]));
            }
        }

        $this->objetos = [$usuario];
        return $this->objetos;
    }

    public function cambiar_contra(int $id_asistente, string $old_pass, string $newpass): void {
        $sql = "SELECT password_hash FROM login_asistente WHERE id_asistente = :id AND status = 'activo'";
        $query = $this->acceso->prepare($sql);
        $query->execute([':id' => $id_asistente]);
        $registro = $query->fetch();

        $verificado = false;
        if ($registro) {
            $info = password_get_info($registro->password_hash);
            $verificado = ($info['algo'] === null || $info['algo'] === 0)
                ? ($old_pass === $registro->password_hash)
                : password_verify($old_pass, $registro->password_hash);
        }

        if ($verificado) {
            $nuevoHash = password_hash($newpass, PASSWORD_BCRYPT, ['cost' => 12]);
            $sql = "UPDATE login_asistente SET password_hash = :newpass WHERE id_asistente = :id";
            $query = $this->acceso->prepare($sql);
            $query->execute([':id' => $id_asistente, ':newpass' => $nuevoHash]);
            echo 'update';
        } else {
            echo 'noupdate';
        }
    }

    public function actualizarUltimoAcceso(int $id_asistente): void {
        $sql = "UPDATE login_asistente SET ultimo_acceso = NOW() WHERE id_asistente = :id";
        $query = $this->acceso->prepare($sql);
        $query->execute([':id' => $id_asistente]);
    }

    private function _actualizarHash(int $id, string $hash): void {
        $sql = "UPDATE login_asistente SET password_hash = :hash WHERE id_asistente = :id";
        $query = $this->acceso->prepare($sql);
        $query->execute([':hash' => $hash, ':id' => $id]);
    }
}
?>
