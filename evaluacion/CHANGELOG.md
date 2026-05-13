# CHANGELOG — Biovital Security Fix
**Fecha de ejecución:** 2026-05-13  
**Ejecutado por:** Auditoría de seguridad automatizada (Bloque A → G)  
**Prompt de referencia:** `evaluacion/09_prompt_correccion.md`

---

## Resumen de cambios

| Bloque | Área | Archivos modificados | Problemas resueltos |
|--------|------|---------------------|---------------------|
| A | Infraestructura | 4 archivos nuevos | PROBLEMA-09, PROBLEMA-14 |
| B | Autenticación | 5 archivos | PROBLEMA-01, PROBLEMA-07, PROBLEMA-08, PROBLEMA-15 |
| C | Autorización | 5 archivos | PROBLEMA-02, PROBLEMA-03 |
| D | CSRF | 8 archivos | PROBLEMA-04 |
| E | Output/Uploads | 14 vistas + 4 controladores | PROBLEMA-05, PROBLEMA-06, PROBLEMA-11 |
| F | Headers/Config | 2 archivos | PROBLEMA-10, PROBLEMA-13 |

---

## BLOQUE A — Infraestructura y configuración

### `.env` — NUEVO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-09 (credenciales hardcodeadas en código fuente)
- **Cambio:** Se externalizan credenciales de BD y configuración de entorno. Variables: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, `APP_ENV`, `APP_URL`.

### `.env.example` — NUEVO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-09
- **Cambio:** Plantilla pública con valores vacíos para guiar configuración en nuevos entornos.

### `.gitignore` — NUEVO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-09, PROBLEMA-14 (datos sensibles en repositorio)
- **Cambio:** Excluye `.env`, `vendor/`, `*.log`, `.DS_Store`, carpetas de IDE, backups SQL.

### `modelo/Conexion.php` — MODIFICADO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-09 (credenciales hardcodeadas)
- **Cambio:** 
  - Se reemplaza `$host = 'localhost'; $user = 'root'; $pass = ''` por lectura de `.env` via `_biovital_load_env()`.
  - Se agrega `PDO::ATTR_EMULATE_PREPARES => false` para forzar prepared statements reales.
  - Se cambia charset a `utf8mb4`.
  - Propiedades tipadas en PHP 7.4+.

---

## BLOQUE B — Autenticación segura

### `modelo/LoginPaciente.php` — MODIFICADO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-01 (contraseñas en texto plano / SQL Injection via password)
- **Cambio:**
  - Se elimina la contraseña del `WHERE` de la query SQL.
  - Se recupera solo el hash almacenado y se verifica con `password_verify()`.
  - Compatibilidad de transición: `password_get_info()` detecta si el hash es texto plano (legado) y lo migra automáticamente a bcrypt en el siguiente login.
  - Rehashing automático si el costo del algoritmo cambió (`password_needs_rehash()`).
  - `cambiar_contra()` actualiza con `password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12])`.

### `modelo/LoginMedico.php` — MODIFICADO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-01
- **Cambio:** Mismo patrón que `LoginPaciente.php` (password_verify + transición + rehashing).

### `modelo/LoginAsistente.php` — MODIFICADO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-01
- **Cambio:** Mismo patrón que `LoginPaciente.php`.

### `modelo/LoginAdministrador.php` — MODIFICADO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-01
- **Cambio:** Mismo patrón que `LoginPaciente.php`.

### `controlador/LoginController.php` — MODIFICADO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-08 (session fixation), PROBLEMA-15 (error_log con datos sensibles)
- **Cambio:**
  - `require_once 'auth_middleware.php'` inicia sesión con parámetros seguros.
  - `session_regenerate_id(true)` llamado inmediatamente tras autenticación exitosa de cada rol.
  - Eliminados todos los `error_log()` que registraban usuario y contraseña.
  - `error_reporting(0)` + `ini_set('display_errors', 0)` en producción.

### `controlador/RegistroPacienteController.php` — MODIFICADO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-07 (registro sin hashear contraseña), PROBLEMA-04 (CSRF en formulario de registro)
- **Cambio:**
  - `password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12])` antes de persistir.
  - `validateCsrf()` para proteger el formulario de registro.
  - Contraseña mínima de 8 caracteres.

### `controlador/RegistroMedicoController.php` — MODIFICADO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-07, PROBLEMA-04
- **Cambio:** Mismo patrón que `RegistroPacienteController.php`.

### `controlador/RegistroAsistenteController.php` — MODIFICADO  
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-07, PROBLEMA-04
- **Cambio:** Mismo patrón que `RegistroPacienteController.php`.

### `controlador/RegistroAdministradorController.php` — MODIFICADO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-07, PROBLEMA-04
- **Cambio:** Mismo patrón que `RegistroPacienteController.php`.

---

## BLOQUE C — Autorización y control de acceso

### `controlador/auth_middleware.php` — NUEVO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-02 (falta de autenticación), PROBLEMA-03 (IDOR), PROBLEMA-04 (CSRF), PROBLEMA-06 (XSS helper), PROBLEMA-10 (sesión insegura)
- **Funciones exportadas:**
  - `requireAuth(?string $rol)` — Verifica sesión activa y rol opcional. Devuelve 401 si no autenticado, 403 si rol incorrecto.
  - `requireOwnership($id_recurso)` — Verifica `$_SESSION['usuario'] === $id_recurso` (excepto administrador). Devuelve 403 si no coincide.
  - `generateCsrfToken()` — Genera o devuelve token CSRF de la sesión (`bin2hex(random_bytes(32))`).
  - `validateCsrf()` — Valida token CSRF con `hash_equals()` para prevenir timing attacks.
  - `h($value)` — Atajo para `htmlspecialchars(..., ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`.
  - Inicialización segura de sesión: `httponly=true`, `secure=true` (en HTTPS), `samesite=Strict`, lifetime=1800s.
  - Renovación de timeout en cada petición.

### `controlador/PacienteController.php` — MODIFICADO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-02, PROBLEMA-03, PROBLEMA-05 (upload RCE), PROBLEMA-04
- **Cambio:**
  - `require_once 'auth_middleware.php'` + `requireAuth('paciente')` al inicio.
  - `validateCsrf()` en todas las operaciones POST de escritura.
  - `requireOwnership($id_paciente)` en edición de perfil y captura de datos.
  - Foto de perfil: `$_SESSION['usuario']` ignora el id enviado por POST (anti-IDOR).
  - Upload: `finfo_file($finfo, $tmp, FILEINFO_MIME_TYPE)` + whitelist `['image/jpeg','image/png','image/webp']` + límite 5MB + nombre aleatorio `bin2hex(random_bytes(16))`.
  - Eliminados `error_log()` con datos sensibles.

### `controlador/MedicoController.php` — MODIFICADO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-02, PROBLEMA-03, PROBLEMA-05, PROBLEMA-04
- **Cambio:** Mismo patrón que `PacienteController.php` con `requireAuth('medico')`.

### `controlador/AsistenteController.php` — MODIFICADO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-02, PROBLEMA-03, PROBLEMA-05, PROBLEMA-04
- **Cambio:** Mismo patrón con `requireAuth('asistente')`.

### `controlador/AdministradorController.php` — MODIFICADO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-02, PROBLEMA-03, PROBLEMA-05, PROBLEMA-04
- **Cambio:** Mismo patrón con `requireAuth('administrador')`. `requireOwnership()` es pasante para administradores (acceso total).

### `controlador/RecetaController.php` — MODIFICADO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-02, PROBLEMA-04
- **Cambio:** Header reemplazado: `requireAuth()` + `validateCsrf()`. Lógica de negocio preservada íntegramente.

### `controlador/ConsultorioController.php` — MODIFICADO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-02, PROBLEMA-04
- **Cambio:** Inyección quirúrgica de `requireAuth('administrador')` + `validateCsrf()` mediante Python `str.replace()`. Las 200+ líneas de lógica existente permanecen sin modificar.

---

## BLOQUE D — Protección CSRF

### `vista/layauts/header.php` — MODIFICADO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-04 (CSRF en peticiones AJAX)
- **Cambio:**
  - Genera `$csrf = generateCsrfToken()` al inicio del archivo.
  - `<meta name="csrf-token" content="...">` con valor escapado con `htmlspecialchars()`.
  - jQuery 3.6.0 con hash SRI (`sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=`).
  - Bootstrap 4.5.2 CSS con hash SRI (`sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z`).
  - `$.ajaxSetup({ beforeSend: ... })` envía `X-CSRF-TOKEN` en todas las peticiones AJAX automáticamente.

### `vista/login_paciente.php` — MODIFICADO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-04
- **Cambio:** CSRF token generado en PHP al inicio; `<input type="hidden" name="csrf_token">` en el formulario.

### `vista/login_medico.php` — MODIFICADO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-04
- **Cambio:** Mismo patrón que `login_paciente.php`.

### `vista/login_asistente.php` — MODIFICADO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-04
- **Cambio:** Mismo patrón.

### `vista/login_administrador.php` — MODIFICADO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-04
- **Cambio:** Mismo patrón.

### `vista/registro_pac.php` — MODIFICADO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-04
- **Cambio:** CSRF token en formulario de registro de pacientes.

### `js/recetas.js` — MODIFICADO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-12 (URL hardcodeada), PROBLEMA-04
- **Cambio:**
  - URL cambiada de `/biovital/controlador/RecetaController.php` a `../../controlador/RecetaController.php` (relativa, portable).
  - CSRF token leído del meta tag e incluido en payload AJAX.

### `js/registro_paciente.js` — MODIFICADO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-04
- **Cambio:** Token CSRF leído de `meta[name="csrf-token"]` e incluido en la petición de registro AJAX.

---

## BLOQUE E — Sanitización de output y uploads

### Vistas modificadas para XSS (14 archivos) — MODIFICADOS
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-06 (XSS reflejado en vistas)
- **Archivos:**
  - `vista/paciente/pac_catalogo.php`
  - `vista/paciente/pac_editar.php`
  - `vista/paciente/pac_perfil.php`
  - `vista/paciente/nav_paciente.php`
  - `vista/medico/med_catalogo.php`
  - `vista/medico/med_editar.php`
  - `vista/medico/nav_medico.php`
  - `vista/asistente/asi_catalogo.php`
  - `vista/asistente/asi_editar.php`
  - `vista/asistente/nav_asistente.php`
  - `vista/administrador/adm_catalogo.php`
  - `vista/administrador/adm_editar.php`
  - `vista/administrador/nav_administrador.php`
  - `vista/layauts/sidebar.php`
- **Cambio:** Script Python aplicó 24 reemplazos:
  - `echo $_SESSION['nombre_us']` → `echo htmlspecialchars($_SESSION['nombre_us'] ?? '', ENT_QUOTES, 'UTF-8')`
  - IDs numéricos en echo: envueltos con `(int)` cast para prevenir inyección de tipo.

---

## BLOQUE F — Headers HTTP y configuración segura

### `.htaccess` — NUEVO
- **Fecha:** 2026-05-13
- **Problemas resueltos:** PROBLEMA-10 (headers HTTP ausentes), PROBLEMA-13 (información del servidor expuesta)
- **Cambio:**
  - `Options -Indexes -ExecCGI` — Deshabilita directory listing y CGI.
  - `ServerSignature Off` — Oculta versión de Apache.
  - Redirección forzada a HTTPS (excepto localhost).
  - `X-Frame-Options: DENY` — Previene clickjacking.
  - `X-Content-Type-Options: nosniff` — Previene MIME sniffing.
  - `Referrer-Policy: strict-origin-when-cross-origin`.
  - `Permissions-Policy` — Deshabilita geolocalización, micrófono, cámara, pagos.
  - `Content-Security-Policy` — Whitelist de dominios para scripts, estilos, fuentes.
  - `Header unset X-Powered-By` y `Header unset Server`.
  - `<FilesMatch>` bloqueando acceso a `.env`, `.log`, `.sql`, `.bak`, `.cfg`, `.ini`, `.md`.
  - `RewriteRule ^ejemplo/` y `^modelo/` → 403 Forbidden.
  - `<LimitExcept GET POST HEAD>` → deniega PUT, DELETE, TRACE, etc.
  - `LimitRequestBody 10485760` (10 MB).
  - PHP flags: `display_errors Off`, `log_errors On`, `session.cookie_httponly 1`, `session.cookie_samesite Strict`.

---

## Estadísticas de la corrección

| Métrica | Valor |
|---------|-------|
| Archivos nuevos creados | 6 |
| Archivos modificados | 32 |
| Vulnerabilidades críticas resueltas | 5 |
| Vulnerabilidades altas resueltas | 6 |
| Vulnerabilidades medias resueltas | 4 |
| Total de problemas cerrados | 15/18 |
| Líneas de código agregadas (aprox.) | ~850 |
| Líneas de código eliminadas (aprox.) | ~120 |

### Problemas fuera de scope de esta ejecución (requieren acción manual):
- **PROBLEMA-16:** Eliminar carpeta `ejemplo/` (archivos de desarrollo expuestos) — requiere confirmación del equipo.
- **PROBLEMA-17:** Imágenes de prueba con fotos reales en `img/` — requiere decisión del propietario.
- **PROBLEMA-18:** Repositorio GitHub debe configurarse como privado — acción manual en GitHub.com.

---

## Próximos pasos recomendados

1. Ejecutar `scripts/migrate_passwords.php` una sola vez en entorno de desarrollo para migrar contraseñas plaintext existentes.
2. Habilitar HSTS en `.htaccess` una vez confirmado HTTPS estable en producción (descomentar la línea `Strict-Transport-Security`).
3. Revisar y eliminar la carpeta `ejemplo/` si no es necesaria.
4. Configurar el repositorio GitHub como privado.
5. Programar auditoría de seguridad en 6 meses.
