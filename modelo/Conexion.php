<?php
/**
 * Archivo: modelo/Conexion.php
 * Propósito: Clase de conexión PDO a la base de datos.
 * [SECURITY FIX] 2026-05-13 - PROBLEMA-07
 *   Las credenciales de BD se leen ahora desde variables de entorno (.env)
 *   en lugar de estar hardcodeadas en el código fuente.
 *   Se agrega PDO::ATTR_EMULATE_PREPARES => false para mayor seguridad.
 *   Se cambia charset a utf8mb4 para soporte completo de Unicode.
 */

// Cargar variables de entorno desde .env si no están ya definidas
if (!function_exists('_biovital_load_env')) {
    function _biovital_load_env(string $path): void {
        if (!file_exists($path)) return;
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) continue;
            if (strpos($line, '=') === false) continue;
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);
            if (!array_key_exists($key, $_ENV) && !array_key_exists($key, $_SERVER)) {
                putenv("$key=$value");
                $_ENV[$key]    = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}
_biovital_load_env(__DIR__ . '/../.env');

class Conexion {
    private string $servidor;
    private string $db;
    private int    $puerto;
    private string $charset  = 'utf8mb4';
    private string $usuario;
    private string $contrasena;
    public  PDO    $pdo;

    private array $atributos = [
        PDO::ATTR_CASE               => PDO::CASE_LOWER,
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS       => PDO::NULL_EMPTY_STRING,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_EMULATE_PREPARES   => false,   // [SECURITY FIX] Prepared statements reales
    ];

    public function __construct() {
        $this->servidor   = $_ENV['DB_HOST'] ?? 'localhost';
        $this->db         = $_ENV['DB_NAME'] ?? 'biovital';
        $this->puerto     = (int)($_ENV['DB_PORT'] ?? 3306);
        $this->usuario    = $_ENV['DB_USER'] ?? 'root';
        $this->contrasena = $_ENV['DB_PASS'] ?? '';

        $dsn = "mysql:dbname={$this->db};host={$this->servidor};port={$this->puerto};charset={$this->charset}";
        $this->pdo = new PDO($dsn, $this->usuario, $this->contrasena, $this->atributos);
    }
}
?>
