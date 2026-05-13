# 08 — Plan de Acción Inmediato

**Sistema:** Biovital | **Fecha:** 2026-05-13

> Este documento es la guía operativa de ejecución. A diferencia del roadmap (estratégico), este plan define las acciones concretas, los archivos específicos a modificar y el orden exacto de ejecución para las primeras 3 semanas críticas.

---

## ⚠️ ACCIÓN PREVIA — Antes de cualquier cambio de código

**Duración:** 2 horas  
**Responsable:** Desarrollador principal o encargado del repositorio

1. **Hacer el repositorio PRIVADO** en GitHub inmediatamente:
   - Settings → Danger Zone → Change repository visibility → Private

2. **Revocar y generar nuevas credenciales** de la base de datos. El usuario `root` con contraseña vacía debe eliminarse del acceso externo. Crear usuario dedicado:
   ```sql
   CREATE USER 'biovital_app'@'localhost' IDENTIFIED BY 'password_fuerte_aqui';
   GRANT SELECT, INSERT, UPDATE, DELETE ON biovital.* TO 'biovital_app'@'localhost';
   FLUSH PRIVILEGES;
   ```

3. **Notificar a los usuarios** que deberán restablecer su contraseña (se explicará por qué en el paso F1.1).

4. **Crear rama de trabajo:**
   ```bash
   git checkout -b security/fase-1-correcciones-criticas
   ```

---

## ACCIÓN 1 — Contraseñas Seguras

**Archivo a modificar:** `modelo/Conexion.php`, todos los modelos de Login, todos los controladores de Registro

**Duración:** 4 horas

### Paso 1.1 — Crear `.env` y cargar con phpdotenv

```bash
composer require vlucas/phpdotenv
```

Crear `/biovital/.env`:
```
DB_HOST=localhost
DB_PORT=3306
DB_NAME=biovital
DB_USER=biovital_app
DB_PASS=password_fuerte_aqui
```

Crear `/biovital/.env.example` (con valores placeholder). Agregar `.env` al `.gitignore`.

### Paso 1.2 — Actualizar `Conexion.php`

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

class Conexion {
    private string $servidor;
    private string $db;
    private int $puerto;
    private string $charset = 'utf8mb4';
    private string $usuario;
    private string $contrasena;
    public PDO $pdo;

    private array $atributos = [
        PDO::ATTR_CASE => PDO::CASE_LOWER,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    public function __construct() {
        $this->servidor   = $_ENV['DB_HOST']  ?? 'localhost';
        $this->db         = $_ENV['DB_NAME']  ?? '';
        $this->puerto     = (int)($_ENV['DB_PORT'] ?? 3306);
        $this->usuario    = $_ENV['DB_USER']  ?? '';
        $this->contrasena = $_ENV['DB_PASS']  ?? '';

        $dsn = "mysql:dbname={$this->db};host={$this->servidor};port={$this->puerto};charset={$this->charset}";
        $this->pdo = new PDO($dsn, $this->usuario, $this->contrasena, $this->atributos);
    }
}
```

### Paso 1.3 — Hashear contraseñas en registro

En cada controlador de registro (`RegistroPacienteController.php`, `RegistroMedicoController.php`, etc.), cambiar:

```php
// ANTES (texto plano)
$paciente->crear(..., $pass, ...);

// DESPUÉS (hash bcrypt)
$hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
$paciente->crear(..., $hash, ...);
```

### Paso 1.4 — Verificar contraseña en login

En cada modelo de Login (`LoginPaciente.php`, `LoginMedico.php`, etc.), cambiar la query para NO incluir `password_hash` en el WHERE, y verificar con `password_verify()`:

```php
function Loguearse($cedula, $pass) {
    // La query ya NO compara contraseña en SQL
    $sql = "SELECT lp.*, rp.nombre_paciente, ...
            FROM login_paciente lp
            INNER JOIN registro_paciente rp ON lp.id_paciente = rp.id_paciente
            WHERE rp.cedula_paciente = :cedula AND lp.status = 'activo'";
    $query = $this->acceso->prepare($sql);
    $query->execute([':cedula' => $cedula]);
    $usuario = $query->fetch();

    if ($usuario && password_verify($pass, $usuario->password_hash)) {
        $this->objetos = [$usuario];
        // Actualizar hash si es necesario (rehashing automático)
        if (password_needs_rehash($usuario->password_hash, PASSWORD_BCRYPT, ['cost' => 12])) {
            $nuevoHash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
            $this->actualizarHash($usuario->id_paciente, $nuevoHash);
        }
        return $this->objetos;
    }
    $this->objetos = [];
    return [];
}
```

### Paso 1.5 — Script de migración de contraseñas existentes

```php
// scripts/migrate_passwords.php — Ejecutar UNA vez y luego eliminar
$pdo = (new Conexion())->pdo;
$tablas = [
    ['login_paciente', 'id_paciente'],
    ['login_medico', 'id_medico'],
    ['login_asistente', 'id_asistente'],
    ['login_administrador', 'id_administrador'],
];
foreach ($tablas as [$tabla, $id_col]) {
    $rows = $pdo->query("SELECT $id_col, password_hash FROM $tabla")->fetchAll();
    foreach ($rows as $row) {
        // Si la contraseña no empieza con $2y$ (bcrypt), hashearla
        if (strpos($row->password_hash, '$2y$') !== 0) {
            $hash = password_hash($row->password_hash, PASSWORD_BCRYPT, ['cost' => 12]);
            $pdo->prepare("UPDATE $tabla SET password_hash = ? WHERE $id_col = ?")
                ->execute([$hash, $row->$id_col]);
        }
    }
}
echo "Migración completada.";
```

---

## ACCIÓN 2 — Middleware de Autenticación y Autorización

**Archivo nuevo:** `controlador/auth_middleware.php`

```php
<?php
function requireAuth(string $rol_requerido = null): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (!isset($_SESSION['usuario']) || !isset($_SESSION['rol'])) {
        http_response_code(401);
        if (isAjax()) {
            echo json_encode(['success' => false, 'error' => 'Sesión no iniciada', 'redirect' => '../index.php']);
        } else {
            header('Location: ../index.php');
        }
        exit();
    }
    if ($rol_requerido !== null && $_SESSION['rol'] !== $rol_requerido) {
        http_response_code(403);
        if (isAjax()) {
            echo json_encode(['success' => false, 'error' => 'Acceso denegado']);
        } else {
            header('Location: ../index.php');
        }
        exit();
    }
}

function requireOwnership(int $id_recurso): void {
    if ($_SESSION['rol'] === 'administrador') return; // Admins pueden todo
    if ((int)$_SESSION['usuario'] !== $id_recurso) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Acceso denegado: recurso de otro usuario']);
        exit();
    }
}

function isAjax(): bool {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}
```

**En cada controlador protegido, agregar al inicio:**

```php
<?php
require_once 'auth_middleware.php';
requireAuth(); // O requireAuth('medico') para rol específico
session_start(); // Ya manejado por el middleware si está activo
```

---

## ACCIÓN 3 — Token CSRF

**En `auth_middleware.php`, agregar:**

```php
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrf(): void {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Token CSRF inválido']);
        exit();
    }
}
```

**En cada vista con formulario:**
```html
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
```

**En JavaScript (peticiones AJAX):**
```javascript
// En header.php, exponer el token de forma segura
<meta name="csrf-token" content="<?= htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">

// En cada petición AJAX:
$.ajaxSetup({
    beforeSend: function(xhr) {
        xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
    }
});
```

---

## ACCIÓN 4 — Sanitizar Output XSS

**Crear función helper `h()` en un archivo incluido globalmente:**

```php
// helpers.php
function h(mixed $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
```

**Reemplazar en todas las vistas:**
```php
// ANTES
echo $_SESSION['nombre_us'];

// DESPUÉS
echo h($_SESSION['nombre_us']);
```

**Archivos que requieren actualización inmediata:**
- `vista/administrador/adm_catalogo.php`
- `vista/paciente/pac_catalogo.php`
- `vista/medico/med_catalogo.php`
- `vista/asistente/asi_catalogo.php`
- Todos los archivos `*_editar_datos.php`
- `vista/layauts/nav_*.php`

---

## ACCIÓN 5 — Seguridad en Subida de Archivos

**En todos los controladores con `cambiar_foto` (PacienteController, MedicoController, etc.):**

```php
if ($funcion == 'cambiar_foto') {
    $id_usuario = (int)$_SESSION['usuario'];

    // Límite de tamaño (5MB)
    if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
        echo json_encode(['alert' => 'noedit', 'message' => 'Archivo demasiado grande']);
        exit();
    }

    // Validación REAL del tipo de archivo con finfo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeReal = $finfo->file($_FILES['photo']['tmp_name']);
    $mimesPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
    $extensiones = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    if (!in_array($mimeReal, $mimesPermitidos)) {
        echo json_encode(['alert' => 'noedit', 'message' => 'Tipo de archivo no permitido']);
        exit();
    }

    // Nombre completamente regenerado (sin nombre original del cliente)
    $extension = $extensiones[$mimeReal];
    $nombre = bin2hex(random_bytes(16)) . '.' . $extension;
    $ruta = '../img/' . $nombre;

    move_uploaded_file($_FILES['photo']['tmp_name'], $ruta);
    // ... resto de la lógica
}
```

---

## ACCIÓN 6 — Headers HTTP y Configuración de Sesión

**Crear/actualizar `.htaccess` en la raíz del proyecto:**

```apache
# Forzar HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Security Headers
Header always set X-Frame-Options "DENY"
Header always set X-Content-Type-Options "nosniff"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
Header always set Content-Security-Policy "default-src 'self'; script-src 'self' https://code.jquery.com https://stackpath.bootstrapcdn.com; style-src 'self' 'unsafe-inline' https://stackpath.bootstrapcdn.com https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; img-src 'self' data:;"

# Bloquear acceso a archivos sensibles
<FilesMatch "\.(env|log|md|py|csv)$">
    Require all denied
</FilesMatch>

# Bloquear acceso a carpeta ejemplo
<DirectoryMatch "ejemplo">
    Require all denied
</DirectoryMatch>
```

**En `auth_middleware.php`, configurar sesión antes de `session_start()`:**
```php
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', '1800'); // 30 minutos
```

---

## Orden de Ejecución Recomendado

```
Día 1:  Repositorio privado → .env → Conexion.php actualizada
Día 2:  password_hash en registro → password_verify en login → script de migración
Día 3:  auth_middleware.php → aplicar requireAuth() en todos los controladores
Día 4:  Fix IDOR → session_regenerate_id → función h() → sanitizar vistas
Día 5:  CSRF tokens → .htaccess → cookie config → pruebas de regresión
```

---

## Verificación Final de Fase 1

Antes de declarar la Fase 1 como completada, verificar manualmente:

- [ ] Registrar un usuario nuevo y confirmar que la contraseña en BD empieza con `$2y$`
- [ ] Intentar acceder a `controlador/MedicoController.php` sin sesión y confirmar 401
- [ ] Intentar editar datos de otro paciente desde la sesión de un paciente diferente y confirmar 403
- [ ] Intentar subir un archivo `.php` como avatar y confirmar rechazo
- [ ] Enviar un POST a un controlador sin token CSRF y confirmar rechazo
- [ ] Verificar que `https://` redirige correctamente
- [ ] Verificar headers de seguridad con `curl -I https://...`
