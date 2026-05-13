<?php
/**
 * Archivo: controlador/auth_middleware.php
 * Propósito: Middleware centralizado de autenticación, autorización y CSRF.
 * Creado: 2026-05-13
 * Motivo: Corrección de PROBLEMA-02, PROBLEMA-03 y PROBLEMA-04
 *
 * Uso básico al inicio de cada controlador protegido:
 *   require_once 'auth_middleware.php';
 *   requireAuth();               // Solo verifica sesión activa
 *   requireAuth('medico');       // Verifica sesión y rol específico
 *   requireOwnership($id);       // Verifica que el recurso pertenece al usuario
 *   validateCsrf();              // Valida token CSRF en peticiones POST
 *   initSession();               // Configura la sesión de forma segura
 */

// ─────────────────────────────────────────────────────────────
// Configuración segura de cookies de sesión
// [SECURITY FIX] 2026-05-13 - PROBLEMA-10
// ─────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    // Configurar antes de session_start()
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => 1800,           // 30 minutos de inactividad
        'path'     => $cookieParams['path'],
        'domain'   => $cookieParams['domain'],
        'secure'   => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,            // Inaccesible desde JavaScript
        'samesite' => 'Strict',        // Protección CSRF adicional
    ]);
    session_start();
}

// ─────────────────────────────────────────────────────────────
// Renovar tiempo de vida de la sesión en cada petición
// ─────────────────────────────────────────────────────────────
if (isset($_SESSION['last_activity'])) {
    if ((time() - $_SESSION['last_activity']) > 1800) {
        // Sesión expirada — destruir
        session_unset();
        session_destroy();
        session_start();
        _biovital_send_unauthorized('Sesión expirada. Por favor inicia sesión nuevamente.');
        exit();
    }
}
$_SESSION['last_activity'] = time();


// ─────────────────────────────────────────────────────────────
// A1 — Verificar autenticación
// [SECURITY FIX] 2026-05-13 - PROBLEMA-02
// ─────────────────────────────────────────────────────────────
/**
 * Verifica que existe una sesión activa y, opcionalmente, que el rol coincide.
 * @param string|null $rol_requerido  Si se pasa, verifica además el rol.
 */
function requireAuth(?string $rol_requerido = null): void {
    if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario']) ||
        !isset($_SESSION['rol'])     || empty($_SESSION['rol'])) {
        http_response_code(401);
        if (_biovital_is_ajax()) {
            echo json_encode([
                'success'  => false,
                'error'    => 'Sesión no iniciada',
                'redirect' => '../index.php'
            ]);
        } else {
            header('Location: ../index.php');
        }
        exit();
    }

    if ($rol_requerido !== null && $_SESSION['rol'] !== $rol_requerido) {
        http_response_code(403);
        if (_biovital_is_ajax()) {
            echo json_encode([
                'success' => false,
                'error'   => 'Acceso denegado: rol insuficiente'
            ]);
        } else {
            header('Location: ../index.php');
        }
        exit();
    }
}


// ─────────────────────────────────────────────────────────────
// A2 — Verificar propiedad de recurso (prevención IDOR)
// [SECURITY FIX] 2026-05-13 - PROBLEMA-03
// ─────────────────────────────────────────────────────────────
/**
 * Verifica que el recurso solicitado pertenece al usuario autenticado.
 * Los administradores siempre tienen acceso.
 * @param int|string $id_recurso  ID del recurso a verificar.
 */
function requireOwnership($id_recurso): void {
    // El administrador tiene acceso a todos los recursos
    if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador') {
        return;
    }

    if ((int)$_SESSION['usuario'] !== (int)$id_recurso) {
        http_response_code(403);
        if (_biovital_is_ajax()) {
            echo json_encode([
                'success' => false,
                'error'   => 'Acceso denegado: este recurso no te pertenece'
            ]);
        } else {
            header('Location: ../index.php');
        }
        exit();
    }
}


// ─────────────────────────────────────────────────────────────
// A3 — Generar token CSRF
// [SECURITY FIX] 2026-05-13 - PROBLEMA-04
// ─────────────────────────────────────────────────────────────
/**
 * Devuelve el token CSRF de la sesión actual, generándolo si no existe.
 * @return string  Token CSRF en hex.
 */
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}


// ─────────────────────────────────────────────────────────────
// A4 — Validar token CSRF
// [SECURITY FIX] 2026-05-13 - PROBLEMA-04
// ─────────────────────────────────────────────────────────────
/**
 * Valida el token CSRF enviado (vía POST o header HTTP).
 * Usa hash_equals para comparación segura contra timing attacks.
 */
function validateCsrf(): void {
    // Aceptar token desde POST o desde header HTTP X-CSRF-TOKEN (AJAX)
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $expected = $_SESSION['csrf_token'] ?? '';

    if (empty($expected) || !hash_equals($expected, $token)) {
        http_response_code(403);
        if (_biovital_is_ajax()) {
            echo json_encode([
                'success' => false,
                'error'   => 'Token de seguridad inválido. Recarga la página e intenta de nuevo.'
            ]);
        } else {
            header('Location: ../index.php?error=csrf');
        }
        exit();
    }
}


// ─────────────────────────────────────────────────────────────
// A5 — Sanitización de output XSS
// [SECURITY FIX] 2026-05-13 - PROBLEMA-06
// ─────────────────────────────────────────────────────────────
/**
 * Escapa HTML para prevenir XSS. Atajo corto para usar en vistas.
 * @param mixed $value  Valor a escapar.
 * @return string       Valor seguro para imprimir en HTML.
 */
function h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}


// ─────────────────────────────────────────────────────────────
// Helpers internos
// ─────────────────────────────────────────────────────────────
function _biovital_is_ajax(): bool {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function _biovital_send_unauthorized(string $mensaje = 'No autorizado'): void {
    http_response_code(401);
    if (_biovital_is_ajax()) {
        header('Content-Type: application/json');
        echo json_encode([
            'success'  => false,
            'error'    => $mensaje,
            'redirect' => '../index.php'
        ]);
    } else {
        header('Location: ../index.php');
    }
}
?>
