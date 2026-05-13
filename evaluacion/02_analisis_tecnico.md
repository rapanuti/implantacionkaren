# 02 — Análisis Técnico General

**Sistema:** Biovital | **Fecha:** 2026-05-13

---

## 1. Arquitectura Actual

El sistema intenta implementar el patrón **MVC (Modelo-Vista-Controlador)** con la siguiente estructura de carpetas:

```
biovital-actualizado/
├── controlador/       ← Controladores HTTP (lógica de negocio + manejo de peticiones)
├── modelo/            ← Clases de acceso a datos (Active Record simplificado)
├── vista/             ← Vistas PHP + HTML
│   ├── administrador/
│   ├── asistente/
│   ├── medico/
│   ├── paciente/
│   └── layauts/       ← [TYPO] Debería ser "layouts"
├── css/
├── js/
├── img/
├── ejemplo/           ← Carpeta de utilidades que NO debería existir en producción
└── index.php
```

### Roles del Sistema
El sistema maneja 4 roles diferenciados:
- **Paciente** (`us_tipo = 1`): Ve sus recetas y datos personales.
- **Médico** (`us_tipo = 2`): Gestiona recetas, pacientes y diagnósticos.
- **Asistente** (`us_tipo = 3`): Rol intermedio con acceso a recetas.
- **Administrador** (`us_tipo = 4`): Gestión global de usuarios y consultorios.

Cada rol tiene su propia tabla de login en base de datos (`login_paciente`, `login_medico`, etc.) y su propia tabla de registro (`registro_paciente`, `registro_medico`, etc.). Este diseño fragmenta los datos de autenticación innecesariamente.

---

## 2. Evaluación del Patrón MVC

### Modelo
- Las clases de modelo (ej. `Paciente.php`, `Medico.php`) extienden directamente la conexión PDO — patrón Active Record funcional pero rudimentario.
- **Anti-patrón crítico:** Los métodos de modelo imprimen su resultado vía `echo` en lugar de retornarlo. Esto fuerza a los controladores a usar `ob_start()`/`ob_get_clean()` para capturar la salida, lo cual es una práctica aberrante en PHP moderno.
- No hay separación entre lógica de negocio y acceso a datos.
- La columna `pdo` es `public` en `Conexion.php`, exponiendo el objeto de conexión.

```php
// ANTI-PATRÓN encontrado en Paciente.php, Medico.php, Receta.php, etc.
function crear(...) {
    // ...
    echo 'add';   // ← Los métodos no retornan, imprimen
}

// En el controlador, forzados a:
ob_start();
$paciente->crear(...);
$resultado = ob_get_clean();  // ← Captura lo impreso
```

### Vista
- Las vistas mezclan PHP con HTML/JS — aceptable en MVC básico, pero sin template engine ni escape de salida.
- Las vistas verifican la sesión directamente con `$_SESSION['us_tipo']` y `$_SESSION['rol']`, lo cual es correcto en concepto, pero insuficiente (ver Seguridad).
- El header (`layauts/header.php`) carga jQuery desde CDN externo sin Subresource Integrity (SRI).
- `<?php echo $_SESSION['nombre_us']; ?>` en múltiples vistas sin `htmlspecialchars()` — vector XSS directo.

### Controlador
- Los controladores actúan como endpoints HTTP/AJAX receptores de `$_POST`.
- **No implementan middleware de autenticación** — cada controlador debería verificar la sesión, pero la mayoría no lo hace.
- La lógica de despacho usa cadenas `if/elseif` sobre `$_POST['funcion']` — un dispatcher manual sin router formal.
- El controlador `RegistroPacienteController.php` tiene `error_reporting(E_ALL)` activado — información de debug expuesta en producción.

---

## 3. Calidad del Código

### Problemas de Estilo y Consistencia
- Mezcla de PHP moderno (null coalescing `??`) con PHP antiguo (`var` para propiedades de clase en lugar de `private`/`protected`).
- Nombres de métodos inconsistentes: `Loguearse()` (mayúscula inicial, español) vs `actualizarUltimoAcceso()` (camelCase).
- Typo en nombre de carpeta: `layauts` en lugar de `layouts`.
- Comentarios de debug dejados en código: `// ← Esto debe ser el ID correcto`, `// DEBUG: Verificar que se guardó`.
- `error_log()` registra información sensible como IDs y nombres de usuarios en texto claro.

### Duplicación Masiva de Código
Los modelos `Paciente`, `Medico`, `Asistente` y `Administrador` son prácticamente idénticos. Toda la lógica de CRUD (crear, editar, cambiar foto, cambiar contraseña) se repite cuatro veces. Esto viola el principio DRY y hace el mantenimiento extremadamente costoso.

### Inconsistencia en Manejo de Errores
- `RecetaController.php` verifica `$_SESSION['usuario']` antes de actuar ✓
- `MedicoController.php` **NO** verifica sesión antes de responder ✗
- `PacienteController.php` **NO** verifica sesión antes de responder ✗
- `AdministradorController.php` **NO** verifica sesión antes de responder ✗

### URLs Hardcodeadas por Entorno
```javascript
// En js/recetas.js — URL hardcodeada para entorno local XAMPP
url: '/biovital/controlador/RecetaController.php',
```
Esto falla en cualquier entorno que no sea una instalación XAMPP local con el proyecto en la carpeta `biovital/`.

---

## 4. Modularidad y Escalabilidad

- **Sin autoloading:** Cada archivo incluye manualmente sus dependencias con `include_once`. Agregar una nueva clase requiere actualizar todos los archivos que la necesiten.
- **Sin namespaces PHP:** Riesgo de colisión de nombres a medida que el proyecto crece.
- **Sin inyección de dependencias:** Las clases instancian directamente sus dependencias (`new Conexion()` dentro de cada modelo), imposibilitando el testing y el mocking.
- **Sin capa de servicios:** Toda la lógica de negocio está en el modelo o en el controlador — no hay separación clara.
- **Sin sistema de configuración:** Variables de conexión hardcodeadas en `Conexion.php`.
- **Sin router:** Los controladores son accedidos directamente por URL — no hay un punto de entrada único (Front Controller).

---

## 5. Infraestructura y Despliegue

- No existe `Dockerfile`, `docker-compose.yml` ni ningún archivo de contenedor.
- No existe pipeline CI/CD (GitHub Actions, GitLab CI, etc.).
- No existe archivo `.env` ni `.env.example`.
- El proyecto está diseñado para correr en **XAMPP local** (evidenciado por el README en `ejemplo/` y las rutas hardcodeadas).
- No hay configuración de servidor web (`.htaccess` para Apache, o configuración Nginx).
- El repositorio es **público en GitHub** (`https://github.com/karenuniversidad2020-oss/biovital-actualizado`), exponiendo toda la arquitectura del sistema.

---

## 6. Base de Datos

La estructura de base de datos tiene las siguientes observaciones (inferidas del código, sin acceso directo al schema):

- Tablas separadas por rol de usuario — diseño con alta duplicación.
- No hay evidencia de índices más allá de las PKs.
- No hay evidencia de constraints de integridad referencial.
- El campo `password_hash` es un nombre engañoso: **las contraseñas se almacenan en texto plano.**
- El campo `status` ('activo'/'inactivo') como string — podría ser ENUM o TINYINT para mayor eficiencia.
- La tabla de recetas relaciona `id_paciente` e `id_medico` — aparentemente sin FK constraints.
- Las estadísticas del dashboard de administrador están hardcodeadas en JS, no provienen de queries reales.

---

## 7. Fortalezas Técnicas

A pesar de los problemas graves, el sistema tiene aspectos positivos:

- Uso correcto de **PDO con prepared statements** — previene inyección SQL en la mayoría de casos.
- **Borrado lógico** de recetas (campo `estado = 0`) — buena práctica en datos médicos.
- Separación de roles con vistas y navegaciones diferenciadas.
- Detección de peticiones AJAX para responder en JSON o redirigir según el caso.
- Registro de `ultimo_acceso` para auditoría básica.
- Estructura base de MVC correctamente identificada — es posible refactorizar sobre ella.
