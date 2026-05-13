# 03 — Análisis de Seguridad

**Sistema:** Biovital | **Sector:** Médico | **Fecha:** 2026-05-13

> Este sistema maneja datos de salud sensibles (recetas, diagnósticos, datos personales de pacientes y médicos). Aplican estándares como HIPAA (si tiene alcance internacional), LOPD/RGPD (si opera en contexto europeo) y equivalentes latinoamericanos. Cualquier brecha de seguridad en este contexto tiene consecuencias legales, financieras y humanas graves.

---

## VULNERABILIDADES CRÍTICAS

---

### [CRÍTICO-01] Contraseñas Almacenadas en Texto Plano

**Archivo:** `modelo/Conexion.php`, todos los modelos de login, `ejemplo/correo_biovital.py`

**Descripción:** La columna se llama `password_hash` pero la función PHP `password_hash()` **nunca es invocada en ningún archivo del proyecto**. Las contraseñas se almacenan y comparan directamente como texto plano en la base de datos.

**Evidencia directa:** El script `ejemplo/correo_biovital.py` lee la contraseña de la base de datos y la envía por correo a los usuarios — esto solo es posible si las contraseñas están en texto plano.

**Impacto:** Si la base de datos es comprometida (por SQL injection, acceso no autorizado al servidor, backup filtrado), **todas las contraseñas de todos los usuarios quedan expuestas inmediatamente** sin necesidad de ningún proceso de cracking. En un sistema médico, esto expone datos de salud confidenciales.

**Corrección:**
```php
// Al registrar:
$hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);

// Al verificar:
if (password_verify($pass_ingresada, $hash_almacenado)) { ... }
```

---

### [CRÍTICO-02] Ausencia Total de Protección CSRF

**Archivo:** Todos los formularios y endpoints de controladores

**Descripción:** Ningún formulario genera ni valida tokens CSRF. Cualquier sitio web externo puede hacer que un usuario autenticado ejecute acciones no deseadas (crear recetas, editar datos de pacientes, eliminar registros) simplemente cargando una imagen o iframe con una petición POST a los controladores de Biovital.

**Impacto:** Un médico autenticado podría, sin saberlo, crear recetas fraudulentas al visitar un enlace malicioso.

**Corrección:**
```php
// En session: $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
// En formulario: <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
// En controlador: verificar que $_POST['csrf_token'] === $_SESSION['csrf_token']
```

---

### [CRÍTICO-03] Sin Verificación de Sesión en la Mayoría de Controladores

**Archivos:** `MedicoController.php`, `PacienteController.php`, `AdministradorController.php`, `AsistenteController.php`

**Descripción:** Estos controladores inician sesión con `session_start()` pero **nunca verifican** que el usuario esté autenticado antes de procesar peticiones. Solo `RecetaController.php` tiene una verificación básica.

Un atacante puede hacer peticiones directas a `PacienteController.php?funcion=buscar_paciente&dato=123` sin tener sesión activa y obtener datos de pacientes.

**Impacto:** Exposición total de datos médicos a usuarios no autenticados.

**Corrección:** Implementar un middleware o función de verificación al inicio de cada controlador:
```php
if (!isset($_SESSION['usuario']) || !isset($_SESSION['rol'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}
```

---

### [CRÍTICO-04] Vulnerabilidades IDOR (Insecure Direct Object Reference)

**Archivos:** `MedicoController.php`, `PacienteController.php`, `AdministradorController.php`

**Descripción:** Los controladores aceptan IDs de recursos desde `$_POST` sin verificar que el recurso pertenezca al usuario autenticado.

```php
// PacienteController.php
if ($funcion == 'editar_paciente') {
    $id_paciente = $_POST['id_paciente'];  // ← Viene del cliente, sin validar
    $paciente->editar($id_paciente, ...);  // ← Edita el registro de CUALQUIER paciente
}
```

Un paciente autenticado puede editar los datos de otro paciente simplemente enviando un `id_paciente` diferente. Lo mismo aplica para médicos, asistentes y administradores.

**Impacto:** Un actor malicioso puede modificar datos médicos de cualquier usuario del sistema.

**Corrección:**
```php
// Verificar que el ID pertenece al usuario autenticado
if ($id_paciente != $_SESSION['usuario'] && $_SESSION['rol'] != 'administrador') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit();
}
```

---

### [CRÍTICO-05] Subida de Archivos sin Validación Real

**Archivos:** `MedicoController.php`, `PacienteController.php`, `AdministradorController.php`, `AsistenteController.php`

**Descripción:** La validación de tipo de archivo usa `$_FILES['photo']['type']`, que es **el MIME type enviado por el cliente** y puede ser falsificado trivialmente. No se usa `finfo_file()` para validar el contenido real del archivo.

```php
// INSEGURO — el 'type' viene del navegador del atacante
if (($_FILES['photo']['type'] == 'image/jpeg') || ...) {
    $nombre = uniqid() . '-' . $_FILES['photo']['name'];  // ← Nombre original del atacante
    move_uploaded_file($_FILES['photo']['tmp_name'], $ruta);  // ← Sube sin validar contenido
}
```

Adicionalmente: no hay validación de tamaño máximo, no se remueve la extensión original del archivo (podría subirse `shell.php.jpg` con nombre `uniqid()-shell.php.jpg`), y el directorio `img/` está dentro del webroot, haciendo los archivos subidos ejecutables por PHP en ciertos servidores.

**Impacto:** Un atacante puede subir un archivo PHP malicioso y ejecutarlo directamente en el servidor (Remote Code Execution).

---

## VULNERABILIDADES ALTAS

---

### [ALTO-01] XSS (Cross-Site Scripting) Reflejado y Almacenado

**Archivos:** Todas las vistas PHP

**Descripción:** Los datos del usuario se muestran directamente desde la sesión o desde la base de datos sin escapar.

```php
// Vista pac_catalogo.php — XSS almacenado
<h4>Bienvenido, <?php echo $_SESSION['nombre_us']; ?></h4>
```

Si el nombre de un usuario contiene `<script>alert(1)</script>`, este se ejecutará en el navegador de cualquier usuario que vea la página.

**Corrección:**
```php
<h4>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_us'], ENT_QUOTES, 'UTF-8'); ?></h4>
```

---

### [ALTO-02] Credenciales de Base de Datos Hardcodeadas

**Archivo:** `modelo/Conexion.php`

```php
private $usuario = "root";
private $contrasena = "";  // ← Sin contraseña
```

Usuario `root` con contraseña vacía. Si alguien obtiene acceso al servidor, tiene control total de MySQL. Las credenciales están en código fuente versionado en un repositorio **público de GitHub**.

---

### [ALTO-03] Sin Regeneración de ID de Sesión tras Login (Session Fixation)

**Archivo:** `controlador/LoginController.php`

**Descripción:** Después de un login exitoso, no se llama a `session_regenerate_id(true)`. Un atacante que conoce el ID de sesión de un usuario antes de que este se autentique puede fijar ese ID y luego obtener una sesión autenticada.

**Corrección:**
```php
// Inmediatamente antes de escribir en $_SESSION tras login exitoso:
session_regenerate_id(true);
```

---

### [ALTO-04] Sin Protección contra Fuerza Bruta en Login

**Archivo:** `controlador/LoginController.php`

**Descripción:** No existe ningún mecanismo de rate limiting, bloqueo de cuenta tras intentos fallidos, CAPTCHA, ni delay progresivo. Un atacante puede intentar millones de combinaciones de cédula/contraseña sin restricción.

**Corrección:** Implementar contador de intentos en sesión o base de datos, bloqueo temporal tras N intentos fallidos.

---

### [ALTO-05] Repositorio Público con Código Sensible

**Evidencia:** `https://github.com/karenuniversidad2020-oss/biovital-actualizado`

El repositorio expone: arquitectura completa del sistema, queries SQL, nombres de tablas, estructura de sesión, y anteriormente podría haber expuesto credenciales. La carpeta `ejemplo/` documenta cómo conectarse a la base de datos y enviar credenciales por correo.

---

### [ALTO-06] Falta de HTTPS / Headers de Seguridad HTTP

**Descripción:** No hay configuración de servidor que fuerce HTTPS. No se envían headers de seguridad HTTP como:
- `Content-Security-Policy`
- `X-Frame-Options`
- `X-Content-Type-Options`
- `Strict-Transport-Security`
- `Referrer-Policy`

En un sistema médico, la transmisión de datos debe ser cifrada obligatoriamente.

---

## VULNERABILIDADES MEDIAS

---

### [MEDIO-01] Datos de Prueba Sensibles en Repositorio

**Carpeta:** `img/`

El directorio de imágenes contiene fotos de figuras políticas reales (`Donald Trump.jpg`, `Ebrahim_Raisi_...jpg`) usadas como avatares de prueba. Estos datos de prueba no deben existir en un repositorio de código.

---

### [MEDIO-02] Logs con Información Sensible

```php
error_log("Médico logueado - ID: " . $objeto->id_medico . ", Nombre: " . $objeto->nombre_medico);
error_log("=== DEPURACIÓN PACIENTE ===");
error_log("ID Paciente: " . $id_paciente);
```

Los logs de debug registran IDs de usuarios y nombres en texto plano. En producción, los logs deben ser estructurados, protegidos y no contener información que identifique personas.

---

### [MEDIO-03] Carpeta `ejemplo/` en Producción

La carpeta contiene:
- Script Python que accede directamente a la base de datos de producción.
- Documentación de la estructura de tablas y credenciales.
- Archivos `.DS_Store` de macOS (metadata del sistema de archivos del desarrollador).

Esta carpeta **debe eliminarse del repositorio y del servidor**.

---

### [MEDIO-04] Cabecera Content-Type Condicional

```php
if($is_ajax) {
    header('Content-Type: application/json');
}
```

El Content-Type solo se establece si la petición es AJAX. Peticiones directas al controlador reciben una respuesta JSON sin Content-Type declarado, lo que puede llevar a MIME sniffing en navegadores antiguos.

---

## VULNERABILIDADES BAJAS

---

### [BAJA-01] Cookie de Sesión sin Flags de Seguridad

No hay configuración explícita de `session.cookie_httponly`, `session.cookie_secure`, ni `session.cookie_samesite`. Las cookies de sesión son accesibles vía JavaScript (riesgo XSS) y se transmiten sobre HTTP.

---

### [BAJA-02] Timeout de Sesión no Implementado

Las sesiones no expiran automáticamente. Un usuario que cierra el navegador sin hacer logout mantiene la sesión activa indefinidamente.

---

### [BAJA-03] Exposición de Información en Respuestas de Error

```php
echo json_encode(['success' => false, 'message' => 'Error al crear la cuenta: ' . $resultado]);
```

Los mensajes de error internos se exponen al cliente, incluyendo posibles trazas de errores PHP o mensajes de base de datos.

---

## Resumen de Vulnerabilidades

| ID | Categoría | Severidad | Estado |
|---|---|---|---|
| CRÍTICO-01 | Contraseñas en texto plano | 🔴 Crítico | Activo |
| CRÍTICO-02 | Sin protección CSRF | 🔴 Crítico | Activo |
| CRÍTICO-03 | Sin verificación de sesión en controladores | 🔴 Crítico | Activo |
| CRÍTICO-04 | IDOR — acceso a recursos de otros usuarios | 🔴 Crítico | Activo |
| CRÍTICO-05 | File upload — RCE potencial | 🔴 Crítico | Activo |
| ALTO-01 | XSS en vistas | 🟠 Alto | Activo |
| ALTO-02 | Credenciales hardcodeadas / root sin pass | 🟠 Alto | Activo |
| ALTO-03 | Session fixation | 🟠 Alto | Activo |
| ALTO-04 | Sin protección brute force | 🟠 Alto | Activo |
| ALTO-05 | Repositorio público con código sensible | 🟠 Alto | Activo |
| ALTO-06 | Sin HTTPS ni security headers | 🟠 Alto | Activo |
| MEDIO-01 | Datos de prueba sensibles en repo | 🟡 Medio | Activo |
| MEDIO-02 | Logs con información sensible | 🟡 Medio | Activo |
| MEDIO-03 | Carpeta `ejemplo/` accesible | 🟡 Medio | Activo |
| MEDIO-04 | Content-Type condicional | 🟡 Medio | Activo |
| BAJA-01 | Cookies sin flags de seguridad | 🟢 Baja | Activo |
| BAJA-02 | Sin timeout de sesión | 🟢 Baja | Activo |
| BAJA-03 | Exposición de errores internos | 🟢 Baja | Activo |
