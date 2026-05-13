<?php
/**
 * scripts/migrate_passwords.php
 * ─────────────────────────────────────────────────────────────
 * SCRIPT DE MIGRACIÓN ÚNICA — Contraseñas plaintext → bcrypt
 * ─────────────────────────────────────────────────────────────
 * Propósito:
 *   Detectar todas las contraseñas almacenadas en texto plano en las
 *   tablas de login y migrarlas a bcrypt (cost=12) de forma segura.
 *
 * IMPORTANTE:
 *   - Ejecutar UNA SOLA VEZ en entorno de desarrollo/staging.
 *   - Hacer backup de la BD antes de ejecutar.
 *   - No incluir este archivo en el directorio público del servidor.
 *   - Eliminar este archivo después de ejecutarlo.
 *
 * Uso desde CLI:
 *   php scripts/migrate_passwords.php
 *
 * Uso desde navegador (solo en desarrollo local):
 *   http://localhost/biovital/scripts/migrate_passwords.php?key=SECRETO
 *
 * Creado: 2026-05-13
 * Referencia: evaluacion/09_prompt_correccion.md — PROBLEMA-01
 * ─────────────────────────────────────────────────────────────
 */

// ── Seguridad básica ─────────────────────────────────────────
// Bloquear acceso desde browser en producción
$cli = (php_sapi_name() === 'cli');

if (!$cli) {
    // En browser, requerir clave de ejecución
    $expected_key = 'biovital_migrate_2026';
    $provided_key = $_GET['key'] ?? '';
    if (!hash_equals($expected_key, $provided_key)) {
        http_response_code(403);
        die("Acceso denegado. Este script solo puede ejecutarse desde CLI o con la clave correcta.");
    }
    echo "<pre>\n";
}

// ── Cargar configuración ─────────────────────────────────────
$env_path = __DIR__ . '/../.env';
if (!file_exists($env_path)) {
    die("ERROR: Archivo .env no encontrado en: $env_path\n");
}

// Cargar .env manualmente
$env_lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($env_lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (strpos($line, '=') === false) continue;
    [$key, $val] = explode('=', $line, 2);
    $_ENV[trim($key)] = trim($val);
}

$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = $_ENV['DB_PORT'] ?? '3306';
$name = $_ENV['DB_NAME'] ?? 'biovital';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';

// ── Conectar a la BD ─────────────────────────────────────────
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    echo "✓ Conexión a BD establecida.\n";
} catch (PDOException $e) {
    die("ERROR de conexión: " . $e->getMessage() . "\n");
}

// ── Tablas y columnas a migrar ───────────────────────────────
$tablas = [
    [
        'tabla'     => 'login_paciente',
        'id_col'    => 'id_paciente',
        'pass_col'  => 'password_hash',
    ],
    [
        'tabla'     => 'login_medico',
        'id_col'    => 'id_medico',
        'pass_col'  => 'password_hash',
    ],
    [
        'tabla'     => 'login_asistente',
        'id_col'    => 'id_asistente',
        'pass_col'  => 'password_hash',
    ],
    [
        'tabla'     => 'login_administrador',
        'id_col'    => 'id_administrador',
        'pass_col'  => 'password_hash',
    ],
];

// ── Ejecutar migración ───────────────────────────────────────
$total_procesados  = 0;
$total_migrados    = 0;
$total_ya_hasheados = 0;
$total_errores     = 0;

echo "\n=== INICIO DE MIGRACIÓN ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

foreach ($tablas as $config) {
    $tabla    = $config['tabla'];
    $id_col   = $config['id_col'];
    $pass_col = $config['pass_col'];

    echo "── Tabla: $tabla ────────────────\n";

    // Verificar que la tabla existe
    try {
        $check = $pdo->query("SHOW TABLES LIKE '$tabla'");
        if ($check->rowCount() === 0) {
            echo "  ⚠ Tabla '$tabla' no encontrada. Saltando.\n\n";
            continue;
        }
    } catch (PDOException $e) {
        echo "  ERROR verificando tabla: " . $e->getMessage() . "\n\n";
        $total_errores++;
        continue;
    }

    // Obtener todos los registros activos
    try {
        $stmt = $pdo->prepare("SELECT $id_col, $pass_col FROM $tabla WHERE status = 'activo'");
        $stmt->execute();
        $registros = $stmt->fetchAll();
    } catch (PDOException $e) {
        echo "  ERROR leyendo registros: " . $e->getMessage() . "\n\n";
        $total_errores++;
        continue;
    }

    $count_tabla      = count($registros);
    $migrados_tabla   = 0;
    $ya_hash_tabla    = 0;

    echo "  Registros activos encontrados: $count_tabla\n";

    foreach ($registros as $reg) {
        $id   = $reg->$id_col;
        $hash = $reg->$pass_col;

        $total_procesados++;

        // Detectar si ya está hasheado con bcrypt
        $info = password_get_info($hash);
        if ($info['algo'] !== null && $info['algo'] !== 0) {
            // Ya tiene hash válido (bcrypt, argon2, etc.)
            $ya_hash_tabla++;
            $total_ya_hasheados++;
            continue;
        }

        // Es texto plano — hashear
        // NOTA: En un escenario real, este script debería pedir la contraseña
        // al administrador o forzar reset. Aquí hashamos el valor actual
        // (que es texto plano) para mantener la compatibilidad inmediata.
        $nuevo_hash = password_hash($hash, PASSWORD_BCRYPT, ['cost' => 12]);

        try {
            $update = $pdo->prepare("UPDATE $tabla SET $pass_col = :hash WHERE $id_col = :id");
            $update->execute([':hash' => $nuevo_hash, ':id' => $id]);
            $migrados_tabla++;
            $total_migrados++;
            echo "  ✓ ID $id migrado a bcrypt.\n";
        } catch (PDOException $e) {
            echo "  ✗ ERROR actualizando ID $id: " . $e->getMessage() . "\n";
            $total_errores++;
        }
    }

    echo "  Resumen tabla '$tabla': $migrados_tabla migrados, $ya_hash_tabla ya hasheados.\n\n";
}

// ── Resumen final ────────────────────────────────────────────
echo "=== RESUMEN FINAL ===\n";
echo "Total procesados:      $total_procesados\n";
echo "Migrados a bcrypt:     $total_migrados\n";
echo "Ya tenían hash válido: $total_ya_hasheados\n";
echo "Errores:               $total_errores\n\n";

if ($total_errores === 0) {
    echo "✓ Migración completada sin errores.\n";
} else {
    echo "⚠ Migración completada con $total_errores error(es). Revisar el log arriba.\n";
}

echo "\n⚠ IMPORTANTE: Elimina este archivo del servidor ahora que la migración está completa.\n";
echo "   rm " . __FILE__ . "\n";

if (!$cli) {
    echo "</pre>\n";
}
?>
