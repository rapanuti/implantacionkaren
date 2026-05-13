# 04 — Pros y Contras

**Sistema:** Biovital | **Fecha:** 2026-05-13

---

## ✅ PROS — Puntos Positivos

### Arquitectura y Diseño
- **Intención MVC clara:** La separación en carpetas `modelo/`, `vista/`, `controlador/` indica comprensión del patrón arquitectónico.
- **PDO con prepared statements:** El acceso a base de datos usa PDO correctamente con parámetros nombrados, lo que previene inyección SQL en la mayoría de operaciones.
- **Separación de roles bien definida:** Los 4 roles (paciente, médico, asistente, administrador) tienen vistas, navegaciones y controladores separados.
- **Borrado lógico:** Las recetas usan `estado = 0/1` en lugar de eliminación física — correcto para datos médicos que deben auditarse.

### Funcionalidad
- **Módulo de recetas funcional:** Creación, edición, borrado lógico, diagnóstico y estudios de laboratorio asociados a recetas.
- **Detección AJAX:** Los controladores detectan si la petición es AJAX y responden con JSON o redirigen apropiadamente.
- **Registro de último acceso:** `actualizarUltimoAcceso()` en todos los modelos de login — base para auditoría.
- **Gestión de avatares:** Sistema de cambio de foto funcional con eliminación del avatar anterior.
- **Validación mínima de formularios:** Los controladores de registro validan campos requeridos y longitud mínima de contraseña.

### UI/UX
- **AdminLTE como framework de UI:** Interfaz administrativa coherente y responsiva.
- **Feedback al usuario:** Mensajes de éxito y error en formularios AJAX.
- **Búsqueda de pacientes en tiempo real:** Debounce implementado en la búsqueda de pacientes para recetas.

### Código
- **Manejo de excepciones PDO:** Los modelos más recientes (`Receta.php`) usan `try/catch` con `PDOException`.
- **Null coalescing (`??`):** Uso moderno de PHP 7+ para valores por defecto.

---

## ❌ CONTRAS — Problemas por Severidad

---

### 🔴 CRÍTICOS — Bloquean completamente la producción

| # | Problema | Impacto |
|---|---|---|
| C-01 | **Contraseñas en texto plano** — La columna se llama `password_hash` pero `password_hash()` nunca se llama. Las contraseñas viajan y se almacenan en texto plano. | Filtración total de credenciales al comprometer la BD |
| C-02 | **Sin protección CSRF** — Ningún formulario ni endpoint tiene token CSRF | Ejecución de acciones no autorizadas vía sitios externos |
| C-03 | **Controladores sin verificación de sesión** — MedicoController, PacienteController, AdministradorController procesan peticiones sin validar autenticación | Acceso no autenticado a datos médicos |
| C-04 | **IDOR generalizado** — IDs de recursos se aceptan del cliente sin verificar propiedad | Un paciente puede modificar datos de cualquier otro paciente |
| C-05 | **Subida de archivos insegura** — Validación de MIME basada en header del cliente (falsificable) sin verificación de contenido real | Remote Code Execution potencial |

---

### 🟠 ALTOS — Requieren corrección urgente antes de producción

| # | Problema | Impacto |
|---|---|---|
| A-01 | **XSS en vistas** — `echo $_SESSION['nombre_us']` sin `htmlspecialchars()` en todas las vistas | Ejecución de JavaScript malicioso en el navegador del usuario |
| A-02 | **Credenciales hardcodeadas** — `root` sin contraseña en `Conexion.php`, en repositorio público | Acceso total a la base de datos |
| A-03 | **Sin regeneración de sesión post-login** — Vulnerable a session fixation | Secuestro de sesión |
| A-04 | **Sin protección brute force** — El login no tiene rate limiting ni bloqueo | Ataques de diccionario contra cuentas |
| A-05 | **Repositorio público en GitHub** — Código fuente, arquitectura y queries expuestos | Facilita ataques dirigidos |
| A-06 | **Sin HTTPS ni security headers** — Datos transmitidos en texto plano | Intercepción de credenciales y datos médicos en red |

---

### 🟡 MEDIOS — Corrección importante para calidad y mantenibilidad

| # | Problema | Impacto |
|---|---|---|
| M-01 | **Anti-patrón `ob_start/ob_get_clean`** — Modelos imprimen en lugar de retornar | Código ilegible, imposible de testear |
| M-02 | **Duplicación masiva de código** — Lógica de CRUD repetida 4 veces (Paciente, Médico, Asistente, Admin) | Mantenimiento 4x más costoso, inconsistencias |
| M-03 | **URLs hardcodeadas en JS** — `/biovital/controlador/...` solo funciona en XAMPP local | Falla en cualquier servidor real |
| M-04 | **Estadísticas del dashboard hardcodeadas** — `$('#total_usuarios').text('4')` en JS | Datos falsos mostrados al administrador |
| M-05 | **Feature incompleta expuesta** — `contarProximasCitas()` retorna siempre 0 | Confunde al usuario sobre funcionalidad del sistema |
| M-06 | **Sin variables de entorno** — Configuración de ambiente en código fuente | Imposible desplegar en múltiples ambientes |
| M-07 | **Carpeta `ejemplo/` en producción** — Contiene scripts de acceso a BD y documentación de credenciales | Exposición de estructura interna del sistema |
| M-08 | **Logs con datos sensibles** — IDs y nombres de usuarios en `error_log()` | Violación de privacidad en logs de sistema |
| M-09 | **Datos de prueba en repositorio** — Imágenes de personas reales como avatares de test | Mala práctica, posibles implicaciones legales |
| M-10 | **Sin autoloading ni namespaces** — `include_once` manual en cada archivo | No escala; colisión de nombres a medida que crece |

---

### 🟢 BAJAS — Buenas prácticas y deuda técnica

| # | Problema | Impacto |
|---|---|---|
| B-01 | **Propiedades con `var` en lugar de `private/protected`** — PHP antiguo (pre-4.0) | Encapsulamiento roto; cualquier código externo puede modificar las propiedades |
| B-02 | **Typo en nombre de carpeta** — `layauts` en lugar de `layouts` | Confusión en el equipo, imposible corregir sin romper referencias |
| B-03 | **Sin timeout de sesión** | Sesiones activas indefinidamente |
| B-04 | **Cookies de sesión sin flags de seguridad** — Sin `HttpOnly`, `Secure`, `SameSite` | Vulnerabilidad adicional ante XSS y ataques CSRF |
| B-05 | **Sin Subresource Integrity** — CDN de jQuery cargado sin hash de verificación | Un CDN comprometido inyecta código malicioso |
| B-06 | **Cero pruebas automatizadas** — Sin PHPUnit, sin tests de integración | Imposible refactorizar con confianza |
| B-07 | **Sin documentación técnica** — No hay README del proyecto, diagrama de BD ni documentación de API | Onboarding costoso para nuevos desarrolladores |
| B-08 | **Sin sistema de monitoreo** — Sin alertas, métricas ni APM | Imposible detectar incidentes en producción |
| B-09 | **Conexión a BD sin pooling** — `new Conexion()` en cada instancia de modelo | Overhead innecesario en alta concurrencia |
| B-10 | **Inconsistencia en manejo de errores** — Algunos archivos muestran errores (`error_reporting(E_ALL)`) y otros los suprimen | Comportamiento impredecible en producción |
