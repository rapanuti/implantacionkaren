# 07 — Checklist Técnico

**Sistema:** Biovital | **Fecha:** 2026-05-13

**Leyenda:**
- ✅ **Cumple** — Implementado correctamente
- ❌ **No cumple** — Ausente o incorrectamente implementado
- ⚠️ **Parcialmente cumple** — Implementado con deficiencias significativas
- 🔍 **Requiere revisión** — No se puede determinar sin acceso al servidor/BD

---

## 1. Arquitectura

| # | Criterio | Estado | Observación |
|---|---|---|---|
| 1.1 | Separación clara de responsabilidades (MVC u otro patrón) | ⚠️ Parcial | Patrón MVC presente pero con mezcla de lógica entre capas |
| 1.2 | Punto de entrada único (Front Controller) | ❌ No cumple | Controladores accesibles directamente por URL |
| 1.3 | Sistema de routing | ❌ No cumple | Sin router; dispatcher manual con `if/elseif` sobre `$_POST['funcion']` |
| 1.4 | Autoloading de clases (PSR-4 / Composer) | ❌ No cumple | Uso de `include_once` manual en cada archivo |
| 1.5 | Namespaces PHP | ❌ No cumple | Sin namespaces; riesgo de colisión de nombres |
| 1.6 | Inyección de dependencias | ❌ No cumple | Las clases instancian sus dependencias directamente |
| 1.7 | Capa de servicios separada de acceso a datos | ❌ No cumple | Lógica de negocio mezclada en modelos y controladores |
| 1.8 | Archivos fuente fuera del webroot | ❌ No cumple | Todo el código PHP es accesible públicamente |
| 1.9 | Configuración por ambiente (.env) | ❌ No cumple | Credenciales hardcodeadas en código fuente |
| 1.10 | Sin duplicación masiva de código (DRY) | ❌ No cumple | Lógica CRUD repetida 4 veces para cada rol |

---

## 2. Calidad de Código

| # | Criterio | Estado | Observación |
|---|---|---|---|
| 2.1 | Nomenclatura consistente (camelCase, PascalCase según contexto) | ⚠️ Parcial | Mezcla de estilos: `Loguearse()` vs `actualizarUltimoAcceso()` |
| 2.2 | Propiedades de clase con visibilidad explícita (`private`/`protected`) | ❌ No cumple | Uso de `var` (PHP antiguo) en todas las clases |
| 2.3 | Métodos de modelo retornan datos (no imprimen con `echo`) | ❌ No cumple | Anti-patrón generalizado: métodos hacen `echo` del resultado |
| 2.4 | Sin código de debug en producción | ❌ No cumple | Múltiples `error_log()` con datos sensibles, comentarios de debug |
| 2.5 | `error_reporting` desactivado consistentemente en producción | ❌ No cumple | `RegistroPacienteController.php` tiene `E_ALL` activo |
| 2.6 | Sin anti-patrones graves (`ob_start` para capturar echo) | ❌ No cumple | Patrón `ob_start/ob_get_clean` en múltiples controladores |
| 2.7 | URLs y rutas configurables, no hardcodeadas | ❌ No cumple | `/biovital/controlador/...` hardcodeado en JS |
| 2.8 | Manejo consistente de errores y excepciones | ⚠️ Parcial | Algunos modelos usan try/catch, otros no |
| 2.9 | Sin typos en nombres de archivos y carpetas | ❌ No cumple | Carpeta `layauts` en lugar de `layouts` |
| 2.10 | Sin archivos de sistema no relacionados (.DS_Store) | ❌ No cumple | `.DS_Store` en carpeta `ejemplo/` |

---

## 3. Seguridad

| # | Criterio | Estado | Observación |
|---|---|---|---|
| 3.1 | Contraseñas hasheadas con algoritmo seguro (bcrypt/argon2) | ❌ No cumple | **CRÍTICO:** Contraseñas en texto plano. Columna engañosamente llamada `password_hash` |
| 3.2 | Protección CSRF en todos los formularios y endpoints POST | ❌ No cumple | Sin tokens CSRF en ningún punto del sistema |
| 3.3 | Verificación de sesión activa en todos los controladores protegidos | ❌ No cumple | Solo `RecetaController` verifica sesión |
| 3.4 | Control de autorización por recurso (prevención de IDOR) | ❌ No cumple | IDs de recursos aceptados del cliente sin verificar propiedad |
| 3.5 | Sanitización de salida HTML (prevención XSS) | ❌ No cumple | Sin `htmlspecialchars()` en ninguna vista |
| 3.6 | Validación de tipo real de archivos subidos (finfo) | ❌ No cumple | Solo valida MIME del cliente (falsificable) |
| 3.7 | Nombres de archivos subidos regenerados (no usar nombre original) | ⚠️ Parcial | Se usa `uniqid()` pero se concatena el nombre original del archivo |
| 3.8 | Regeneración de ID de sesión tras login (prevención session fixation) | ❌ No cumple | Sin `session_regenerate_id()` |
| 3.9 | Protección contra fuerza bruta en login | ❌ No cumple | Sin rate limiting, bloqueo de cuenta ni CAPTCHA |
| 3.10 | Prepared statements en todas las queries SQL | ✅ Cumple | PDO con parámetros nombrados en todas las queries |
| 3.11 | Headers HTTP de seguridad (CSP, X-Frame-Options, etc.) | ❌ No cumple | Sin headers de seguridad configurados |
| 3.12 | HTTPS forzado | 🔍 Requiere revisión | No hay configuración de servidor visible; diseñado para HTTP local |
| 3.13 | Cookies de sesión con flags `HttpOnly`, `Secure`, `SameSite` | ❌ No cumple | Sin configuración explícita de cookies |
| 3.14 | Credenciales fuera del código fuente y del control de versiones | ❌ No cumple | `root` sin contraseña hardcodeado en `Conexion.php`, en repo público |
| 3.15 | Sin datos sensibles en repositorio público | ❌ No cumple | Repo público, imágenes de personas reales, scripts de acceso a BD |
| 3.16 | Sin exposición de información técnica en errores al cliente | ⚠️ Parcial | Algunos controladores exponen mensajes de error internos |
| 3.17 | Logs sin información personal identificable | ❌ No cumple | Logs con IDs de usuarios, nombres y datos de debug |
| 3.18 | Subresource Integrity en recursos de CDN externos | ❌ No cumple | jQuery cargado desde CDN sin hash SRI |

---

## 4. Base de Datos

| # | Criterio | Estado | Observación |
|---|---|---|---|
| 4.1 | Usuario de BD con permisos mínimos necesarios (no root) | ❌ No cumple | Usuario `root` con contraseña vacía |
| 4.2 | Contraseñas de BD en variables de entorno | ❌ No cumple | Hardcodeadas en `Conexion.php` |
| 4.3 | Indexes en columnas de búsqueda frecuente | 🔍 Requiere revisión | Sin acceso al schema de BD |
| 4.4 | Constraints de integridad referencial (FK) | 🔍 Requiere revisión | Sin evidencia en código; probablemente ausentes |
| 4.5 | Datos sensibles cifrados en reposo | ❌ No cumple | Sin cifrado; contraseñas en texto plano |
| 4.6 | Estrategia de backup definida | 🔍 Requiere revisión | Sin evidencia de configuración de backups |
| 4.7 | Soft delete implementado en datos críticos | ✅ Cumple | Recetas usan `estado = 0/1` para borrado lógico |
| 4.8 | Schema de BD documentado | ❌ No cumple | Sin diagrama ER ni documentación de tablas |
| 4.9 | Migraciones de BD versionadas | ❌ No cumple | Sin sistema de migraciones |
| 4.10 | Sin queries dinámicas con concatenación de strings | ✅ Cumple | Todas las queries usan parámetros PDO |

---

## 5. Infraestructura y Despliegue

| # | Criterio | Estado | Observación |
|---|---|---|---|
| 5.1 | Containerización (Docker) | ❌ No cumple | Sin Dockerfile ni docker-compose |
| 5.2 | Pipeline CI/CD | ❌ No cumple | Sin GitHub Actions ni equivalente |
| 5.3 | Ambientes separados (dev / staging / production) | ❌ No cumple | Solo existe ambiente local XAMPP |
| 5.4 | Variables de entorno por ambiente | ❌ No cumple | Sin gestión de ambientes |
| 5.5 | Servidor web configurado correctamente (Apache/Nginx) | ❌ No cumple | Sin `.htaccess` ni config Nginx |
| 5.6 | Archivos de configuración fuera del webroot | ❌ No cumple | Todo accesible por URL |
| 5.7 | Repositorio privado | ❌ No cumple | Repositorio público en GitHub |
| 5.8 | Secrets gestionados de forma segura (no en código) | ❌ No cumple | Credenciales en código fuente versionado |

---

## 6. Testing

| # | Criterio | Estado | Observación |
|---|---|---|---|
| 6.1 | Tests unitarios | ❌ No cumple | Sin ningún test automatizado |
| 6.2 | Tests de integración | ❌ No cumple | Ausentes |
| 6.3 | Tests de seguridad automatizados | ❌ No cumple | Ausentes |
| 6.4 | Cobertura de código medida | ❌ No cumple | Sin métricas de cobertura |
| 6.5 | Tests ejecutados en CI antes de merge | ❌ No cumple | Sin CI |

---

## 7. Monitoreo y Logging

| # | Criterio | Estado | Observación |
|---|---|---|---|
| 7.1 | Sistema de logging estructurado | ❌ No cumple | Solo `error_log()` nativo con strings concatenados |
| 7.2 | Niveles de log diferenciados (DEBUG, INFO, WARNING, ERROR) | ❌ No cumple | Sin diferenciación de niveles |
| 7.3 | Logs sin información personal identificable | ❌ No cumple | Logs contienen IDs, nombres y datos de debug |
| 7.4 | Rotación de logs configurada | 🔍 Requiere revisión | No visible en código |
| 7.5 | Monitoreo de errores en producción (Sentry / similar) | ❌ No cumple | Ausente |
| 7.6 | Alertas automáticas ante errores críticos | ❌ No cumple | Ausente |
| 7.7 | Auditoría de acciones sensibles (quién editó qué y cuándo) | ⚠️ Parcial | Solo `ultimo_acceso`; sin log de cambios en datos |
| 7.8 | Health check endpoint | ❌ No cumple | Ausente |

---

## 8. Documentación

| # | Criterio | Estado | Observación |
|---|---|---|---|
| 8.1 | README del proyecto con instrucciones de instalación | ❌ No cumple | Solo existe un README en la carpeta `ejemplo/`, no del sistema |
| 8.2 | Documentación de API / endpoints | ❌ No cumple | Sin documentación de los controladores/endpoints |
| 8.3 | Diagrama de arquitectura | ❌ No cumple | Ausente |
| 8.4 | Diagrama de base de datos (ER) | ❌ No cumple | Ausente |
| 8.5 | Guía de contribución (CONTRIBUTING.md) | ❌ No cumple | Ausente |
| 8.6 | Changelog versionado | ❌ No cumple | Ausente |
| 8.7 | Documentación de seguridad y decisiones de arquitectura | ❌ No cumple | Ausente |

---

## Resumen del Checklist

| Dimensión | Cumple | Parcial | No Cumple | Requiere Revisión |
|---|---|---|---|---|
| Arquitectura (10) | 0 | 2 | 8 | 0 |
| Calidad de código (10) | 0 | 2 | 8 | 0 |
| Seguridad (18) | 2 | 3 | 12 | 1 |
| Base de datos (10) | 2 | 0 | 5 | 3 |
| Infraestructura (8) | 0 | 0 | 8 | 0 |
| Testing (5) | 0 | 0 | 5 | 0 |
| Monitoreo (8) | 0 | 2 | 5 | 1 |
| Documentación (7) | 0 | 0 | 7 | 0 |
| **TOTAL (76)** | **4 (5%)** | **9 (12%)** | **58 (76%)** | **5 (7%)** |

> **Tasa de cumplimiento actual: 5%** (4 de 76 criterios completamente cumplidos)
