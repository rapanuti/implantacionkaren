<?php
/**
 * [SECURITY FIX] 2026-05-13 - PROBLEMA-01
 *   Loguearse() ya NO compara contraseña en la query SQL.
 *   Se usa password_verify() para verificar el hash bcrypt de forma segura.
 *   Soporta transición: detecta contraseñas aún en texto plano y las migra al vuelo.
 *   Se agrega rehashing automático si el costo del algoritmo cambió.
 */
include_once 'Conexion.php';

class LoginPaciente {
    protected $objetos;
    protected $acceso;

    public function __construct() {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }

    /**
     * [SECURITY FIX] 2026-05-13 - PROBLEMA-01
     * Contraseña removida del WHERE de la query. Se verifica con password_verify().
     */
    public function Loguearse(string $cedula, string $pass): array {
        $sql = "SELECT lp.*, rp.nombre_paciente, rp.apellido_paciente,
                       rp.paciente_tipo, tp.nombre_tipo
                FROM login_paciente lp
                INNER JOIN registro_paciente rp ON lp.id_paciente = rp.id_paciente
                INNER JOIN tipo_paciente tp ON rp.paciente_tipo = tp.id_tipo_us
                WHERE rp.cedula_paciente = :cedula AND lp.status = 'activo'";
        $query = $this->acceso->prepare($sql);
        $query->execute([':cedula' => $cedula]);
        $usuario = $query->fetch();

        if (!$usuario) {
            $this->objetos = [];
            return [];
        }

        $hash = $usuario->password_hash;

        // Compatibilidad de transición: si el hash NO empieza con '$2y$' (bcrypt),
        // aún es texto plano — comparamos directo y migramos al vuelo.
        $info = password_get_info($hash);
        if ($info['algo'] === null || $info['algo'] === 0) {
            // Contraseña en texto plano (legado)
            if ($pass !== $hash) {
                $this->objetos = [];
                return [];
            }
            // Migrar a bcrypt
            $nuevoHash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
            $this->_actualizarHash($usuario->id_paciente, $nuevoHash);
        } else {
            // Contraseña hasheada — verificación segura
            if (!password_verify($pass, $hash)) {
                $this->objetos = [];
                return [];
            }
            // Rehashing si el costo del algoritmo cambió
            if (password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12])) {
                $nuevoHash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
                $this->_actualizarHash($usuario->id_paciente, $nuevoHash);
            }
        }

        $this->objetos = [$usuario];
        return $this->objetos;
    }

    public function cambiar_contra(int $id_paciente, string $old_pass, string $newpass): void {
        $sql = "SELECT password_hash FROM login_paciente WHERE id_paciente = :id AND status = 'activo'";
        $query = $this->acceso->prepare($sql);
        $query->execute([':id' => $id_paciente]);
        $registro = $query->fetch();

        $verificado = false;
        if ($registro) {
            $info = password_get_info($registro->password_hash);
            if ($info['algo'] === null || $info['algo'] === 0) {
                $verificado = ($old_pass === $registro->password_hash); // legado
            } else {
                $verificado = password_verify($old_pass, $registro->password_hash);
            }
        }

        if ($verificado) {
            // [SECURITY FIX] Nueva contraseña se hashea con bcrypt
            $nuevoHash = password_hash($newpass, PASSWORD_BCRYPT, ['cost' => 12]);
            $sql = "UPDATE login_paciente SET password_hash = :newpass WHERE id_paciente = :id";
            $query = $this->acceso->prepare($sql);
            $query->execute([':id' => $id_paciente, ':newpass' => $nuevoHash]);
            echo 'update';
        } else {
            echo 'noupdate';
        }
    }

    public function actualizarUltimoAcceso(int $id_paciente): void {
        $sql = "UPDATE login_paciente SET ultimo_acceso = NOW() WHERE id_paciente = :id";
        $query = $this->acceso->prepare($sql);
        $query->execute([':id' => $id_paciente]);
    }

    /** Actualiza el hash almacenado (migración/rehashing interno). */
    private function _actualizarHash(int $id, string $hash): void {
        $sql = "UPDATE login_paciente SET password_hash = :hash WHERE id_paciente = :id";
        $query = $this->acceso->prepare($sql);
        $query->execute([':hash' => $hash, ':id' => $id]);
    }
}
?>
