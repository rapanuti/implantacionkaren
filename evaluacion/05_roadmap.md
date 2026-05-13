# 05 — Roadmap de Mejora

**Sistema:** Biovital | **Fecha:** 2026-05-13

> El roadmap está organizado en tres fases. Las fases son secuenciales en términos de seguridad (Fase 1 es prerrequisito para Fase 2), pero algunos ítems de Fase 2 y 3 pueden ejecutarse en paralelo por diferentes desarrolladores.

---

## FASE 1 — Correcciones Críticas de Seguridad
**Plazo estimado:** Semanas 1–3  
**Prioridad:** BLOQUEANTE — El sistema no debe usarse con datos reales hasta completar esta fase.

---

### F1.1 — Implementar hashing correcto de contraseñas
**Prioridad:** 🔴 Máxima  
**Dependencias:** Ninguna  
**Esfuerzo:** Medio (requiere migración de datos existentes)

- Implementar `password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12])` en el registro.
- Implementar `password_verify($pass, $hash)` en el login para todos los roles.
- Crear script de migración para hashear contraseñas existentes en BD (pedir a usuarios que restablezcan su contraseña es la alternativa más segura).
- Renombrar la columna `password_hash` en la BD para que realmente contenga el hash.

---

### F1.2 — Implementar middleware de autenticación y autorización
**Prioridad:** 🔴 Máxima  
**Dependencias:** Ninguna  
**Esfuerzo:** Medio

- Crear un archivo `auth_middleware.php` con función reutilizable de verificación de sesión.
- Incluir y llamar el middleware al inicio de cada controlador protegido.
- Verificar que el ID de recurso manipulado pertenezca al usuario en sesión (fix IDOR).
- Implementar `session_regenerate_id(true)` tras login exitoso.

---

### F1.3 — Implementar protección CSRF
**Prioridad:** 🔴 Máxima  
**Dependencias:** F1.2 (requiere sesión activa para el token)  
**Esfuerzo:** Bajo-Medio

- Generar token CSRF al iniciar sesión y almacenarlo en `$_SESSION['csrf_token']`.
- Incluir el token en todos los formularios como campo oculto.
- Incluir el token en todas las peticiones AJAX desde JavaScript.
- Validar el token al inicio de cada controlador que recibe POST.

---

### F1.4 — Asegurar subida de archivos
**Prioridad:** 🔴 Máxima  
**Dependencias:** Ninguna  
**Esfuerzo:** Bajo

- Usar `finfo_file()` para validar el tipo MIME real del archivo (no el del cliente).
- Whitelist de extensiones permitidas: solo `.jpg`, `.jpeg`, `.png`, `.webp`.
- Regenerar el nombre del archivo completamente (sin usar el nombre original).
- Mover el directorio de uploads fuera del webroot o configurar que no sean ejecutables.
- Implementar límite de tamaño máximo de archivo.

---

### F1.5 — Separar credenciales del código fuente
**Prioridad:** 🔴 Máxima  
**Dependencias:** Ninguna  
**Esfuerzo:** Bajo

- Crear archivo `.env` con las credenciales de BD.
- Crear `.env.example` con valores de placeholder.
- Agregar `.env` al `.gitignore`.
- Usar `vlucas/phpdotenv` o equivalente para cargar variables de entorno.
- Configurar usuario de BD dedicado con permisos mínimos (no root).
- Cambiar la contraseña de la BD.

---

### F1.6 — Implementar headers de seguridad HTTP y forzar HTTPS
**Prioridad:** 🟠 Alta  
**Dependencias:** Ninguna  
**Esfuerzo:** Bajo

- Configurar `.htaccess` o configuración Nginx para forzar HTTPS.
- Implementar headers: `Content-Security-Policy`, `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Strict-Transport-Security`, `Referrer-Policy`.
- Configurar cookies de sesión: `session.cookie_httponly = true`, `session.cookie_secure = true`, `session.cookie_samesite = Strict`.

---

### F1.7 — Implementar protección contra fuerza bruta
**Prioridad:** 🟠 Alta  
**Dependencias:** F1.2  
**Esfuerzo:** Bajo-Medio

- Crear tabla `intentos_login(ip, cedula, intentos, bloqueado_hasta)` en BD.
- Bloquear por 15 minutos tras 5 intentos fallidos.
- Opcional: implementar CAPTCHA (reCAPTCHA v3) tras 3 intentos.

---

### F1.8 — Sanitizar salida en vistas (fix XSS)
**Prioridad:** 🟠 Alta  
**Dependencias:** Ninguna  
**Esfuerzo:** Bajo (repetitivo)

- Envolver todos los `echo $_SESSION[...]` y `echo $objeto->campo` con `htmlspecialchars($valor, ENT_QUOTES, 'UTF-8')`.
- Crear función helper `h($str)` como atajo.
- Revisar outputs en JavaScript embebido en PHP.

---

### F1.9 — Limpiar el repositorio
**Prioridad:** 🟠 Alta  
**Dependencias:** F1.5  
**Esfuerzo:** Bajo

- Eliminar carpeta `ejemplo/` del repositorio.
- Eliminar imágenes de prueba de personas reales de `img/`.
- Hacer el repositorio **privado** inmediatamente.
- Revocar y regenerar cualquier credencial que haya estado en el historial de git.

---

## FASE 2 — Refactorización y Calidad de Código
**Plazo estimado:** Semanas 4–7  
**Prioridad:** Alta — Necesaria para mantenibilidad y escalabilidad

---

### F2.1 — Refactorizar el anti-patrón `echo` en modelos
**Prioridad:** Alta  
**Dependencias:** F1 completa  
**Esfuerzo:** Alto

- Cambiar todos los métodos de modelo para que `return` datos en lugar de `echo`.
- Eliminar el uso de `ob_start()`/`ob_get_clean()` en controladores.
- Estandarizar respuestas: los métodos retornan datos o lanzan excepciones.

---

### F2.2 — Implementar autoloading con Composer
**Prioridad:** Alta  
**Dependencias:** F2.1  
**Esfuerzo:** Bajo

- Inicializar Composer (`composer init`).
- Configurar PSR-4 autoloading en `composer.json`.
- Eliminar todos los `include_once` manuales.
- Agregar `vendor/` al `.gitignore`.

---

### F2.3 — Crear clase base de modelo para eliminar duplicación
**Prioridad:** Media  
**Dependencias:** F2.1, F2.2  
**Esfuerzo:** Alto

- Crear `BaseModel` con métodos comunes: `cambiar_foto()`, `cambiar_contra()`, `obtener_datos()`, `editar()`.
- Hacer que `Paciente`, `Medico`, `Asistente`, `Administrador` extiendan `BaseModel`.
- Eliminar duplicación de ~70% del código de modelos.

---

### F2.4 — Implementar un Front Controller y Router básico
**Prioridad:** Media  
**Dependencias:** F2.2  
**Esfuerzo:** Alto

- Crear `public/index.php` como punto de entrada único.
- Mover archivos públicos (CSS, JS, img) a `public/`.
- Mover controladores, modelos y vistas fuera del webroot.
- Implementar router simple o usar `bramus/router` vía Composer.

---

### F2.5 — Variables de configuración centralizadas
**Prioridad:** Alta  
**Dependencias:** F1.5, F2.2  
**Esfuerzo:** Bajo

- Centralizar URLs base, rutas de uploads y configuración de sesión en un archivo `config.php`.
- Eliminar URLs hardcodeadas de archivos JS (usar variables PHP en el HTML o configuración dinámica).

---

### F2.6 — Implementar sesiones con timeout
**Prioridad:** Media  
**Dependencias:** F1.2  
**Esfuerzo:** Bajo

- Configurar expiración de sesión por inactividad (30 minutos).
- Alertar al usuario antes de que expire con un modal de advertencia.

---

### F2.7 — Implementar logs estructurados
**Prioridad:** Media  
**Dependencias:** F2.2  
**Esfuerzo:** Bajo-Medio

- Reemplazar `error_log()` manual con Monolog.
- Definir canales de log: `security`, `app`, `database`.
- Eliminar información sensible de los logs.
- Configurar rotación de logs.

---

## FASE 3 — Madurez Técnica y Escalabilidad
**Plazo estimado:** Semanas 8–12  
**Prioridad:** Media — Para sistema listo para producción sostenible

---

### F3.1 — Suite de pruebas automatizadas
**Esfuerzo:** Alto

- Instalar PHPUnit.
- Escribir tests unitarios para todos los modelos.
- Escribir tests de integración para los flujos críticos (login, creación de receta, edición de perfil).
- Integrar en pipeline CI.

---

### F3.2 — Containerización con Docker
**Esfuerzo:** Medio

- Crear `Dockerfile` para la aplicación PHP.
- Crear `docker-compose.yml` con servicios: `app` (PHP-FPM), `db` (MySQL), `nginx`.
- Crear ambientes diferenciados: `development`, `staging`, `production`.

---

### F3.3 — Pipeline CI/CD
**Esfuerzo:** Medio

- Configurar GitHub Actions (o equivalente).
- Pipeline: lint → tests → build → deploy.
- Secrets de ambiente en el gestor de secretos del CI.

---

### F3.4 — Implementar sistema de auditoría de datos médicos
**Esfuerzo:** Alto

- Crear tabla `audit_log(tabla, registro_id, accion, usuario_id, datos_anteriores, datos_nuevos, timestamp, ip)`.
- Registrar automáticamente toda modificación de datos de pacientes, recetas y diagnósticos.
- Esto es un requisito regulatorio en muchos sistemas de salud.

---

### F3.5 — Unificar modelo de usuarios
**Esfuerzo:** Alto

- Evaluar la consolidación de las 8 tablas de usuarios (4 de registro + 4 de login) en un esquema unificado con roles.
- Schema propuesto: `usuarios(id, cedula, nombre, apellido, email, password_hash, rol_id, status, created_at)`.
- Reducir complejidad y facilitar consultas cross-rol.

---

### F3.6 — Monitoreo y observabilidad
**Esfuerzo:** Medio

- Implementar health check endpoint.
- Integrar con herramienta de APM (Sentry, New Relic, o Datadog).
- Configurar alertas para errores críticos.
- Dashboard de métricas de uso.

---

### F3.7 — Completar features incompletas
**Esfuerzo:** Medio

- Implementar sistema de citas médicas (`contarProximasCitas()` actualmente retorna 0).
- Hacer dinámicas las estadísticas del dashboard de administrador.
- Corregir el typo `layauts` → `layouts` con un script de migración controlado.
