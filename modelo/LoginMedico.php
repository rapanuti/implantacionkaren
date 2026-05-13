<?php
/**
 * [SECURITY FIX] 2026-05-13 - PROBLEMA-01
 *   Contraseña eliminada del WHERE de la query SQL.
 *   password_verify() con compatibilidad de transición (texto plano -> bcrypt).
 */
include_once 'Conexion.php';

class LoginMedico {
    protected $objetos;
    protected $acceso;

    public function __construct() {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }

    public function Loguearse(string $cedula, string $pass): array {
        $sql = "SELECT lm.*, rm.nombre_medico, rm.apellido_medico,
                       rm.medico_tipo, tp.nombre_tipo
                FROM login_medico lm
                INNER JOIN registro_medico rm ON lm.id_medico = rm.id_medico
                INNER JOIN tipo_paciente tp ON rm.medico_tipo = tp.id_tipo_us
                WHERE rm.cedula_medico = :cedula AND lm.status = 'activo'";
        $query = $this->acceso->prepare($sql);
        $query->execute([':cedula' => $cedula]);
        $usuario = $query->fetch();

        if (!$usuario) { $this->objetos = []; return []; }

        $hash = $usuario->password_hash;
        $info = password_get_info($hash);

        if ($info['algo'] === null || $info['algo'] === 0) {
            if ($pass !== $hash) { $this->objetos = []; return []; }
            $this->_actualizarHash($usuario->id_medico, password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]));
        } else {
            if (!password_verify($pass, $hash)) { $this->objetos = []; return []; }
            if (password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12])) {
                $this->_actualizarHash($usuario->id_medico, password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]));
            }
        }

        $this->objetos = [$usuario];
        return $this->objetos;
    }

    public function cambiar_contra(int $id_medico, string $old_pass, string $newpass): void {
        $sql = "SELECT password_hash FROM login_medico WHERE id_medico = :id AND status = 'activo'";
        $query = $this->acceso->prepare($sql);
        $query->execute([':id' => $id_medico]);
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
            $sql = "UPDATE login_medico SET password_hash = :newpass WHERE id_medico = :id";
            $query = $this->acceso->prepare($sql);
            $query->execute([':id' => $id_medico, ':newpass' => $nuevoHash]);
            echo 'update';
        } else {
            echo 'noupdate';
        }
    }

    public function actualizarUltimoAcceso(int $id_medico): void {
        $sql = "UPDATE login_medico SET ultimo_acceso = NOW() WHERE id_medico = :id";
        $query = $this->acceso->prepare($sql);
        $query->execute([':id' => $id_medico]);
    }

    private function _actualizarHash(int $id, string $hash): void {
        $sql = "UPDATE login_medico SET password_hash = :hash WHERE id_medico = :id";
        $query = $this->acceso->prepare($sql);
        $query->execute([':hash' => $hash, ':id' => $id]);
    }
}
?>
