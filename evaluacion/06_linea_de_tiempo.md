# 06 — Línea de Tiempo Estimada

**Sistema:** Biovital | **Fecha:** 2026-05-13

> Estimaciones basadas en **1 desarrollador backend con experiencia en PHP y seguridad web**. Con 2 desarrolladores, la Fase 1 puede completarse en 1.5 semanas. Los esfuerzos marcados como "Alto" asumen implementación + testing manual de regresión.

---

## Vista General

```
Semana  1    2    3    4    5    6    7    8    9   10   11   12
        ├────────────┤
FASE 1  │ Seguridad Crítica (3 sem)        │
        └────────────┤────────────────────┤
FASE 2               │ Refactorización (4 sem)      │
                     └────────────────────┤──────────────────┤
FASE 3                                    │ Madurez Técnica (5 sem)│
                                          └──────────────────┘
```

---

## FASE 1 — Semanas 1 a 3

### Semana 1 — Seguridad Inmediata (Sin excusas)

| Día | Tarea | Esfuerzo | Entregable |
|---|---|---|---|
| Lun | **F1.9** Hacer privado el repo, eliminar `ejemplo/` e imágenes sensibles | Bajo | Repo privado, historial limpio |
| Lun | **F1.5** Crear `.env`, `.env.example`, `.gitignore`, usuario BD dedicado | Bajo | Config externalizada |
| Mar | **F1.1** Implementar `password_hash()` en registro de los 4 roles | Medio | Nuevos registros con hash |
| Mar | **F1.1** Script de migración de contraseñas existentes (forzar reset) | Medio | BD migrada |
| Mié | **F1.2** Crear `auth_middleware.php`, aplicar en todos los controladores | Medio | Endpoints protegidos |
| Jue | **F1.2** Fix IDOR: verificar propiedad del recurso en editar/foto | Medio | Autorización por recurso |
| Jue | **F1.2** `session_regenerate_id(true)` en LoginController | Bajo | Session fixation corregida |
| Vie | **F1.8** Sanitizar output XSS en todas las vistas con `htmlspecialchars()` | Bajo | Vistas seguras |
| Vie | **Revisión y pruebas de regresión manual** | — | Funcionalidad intacta |

**Entregable Semana 1:** Sistema con autenticación segura básica. Las vulnerabilidades CRÍTICO-01, CRÍTICO-03, CRÍTICO-04 y ALTO-01 corregidas.

---

### Semana 2 — CSRF, Uploads y Fuerza Bruta

| Día | Tarea | Esfuerzo | Entregable |
|---|---|---|---|
| Lun | **F1.3** Implementar generación de token CSRF en sesión | Bajo | Token generado en login |
| Lun–Mar | **F1.3** Agregar token CSRF a todos los formularios y peticiones AJAX | Medio | Formularios protegidos |
| Mar | **F1.3** Validar token CSRF al inicio de cada controlador POST | Bajo | Validación activa |
| Mié | **F1.4** Validación real de MIME con `finfo_file()` en subida de archivos | Bajo | Upload seguro |
| Mié | **F1.4** Regenerar nombres de archivo, whitelist de extensiones, límites de tamaño | Bajo | Upload hardening |
| Jue | **F1.7** Tabla `intentos_login`, lógica de bloqueo en LoginController | Medio | Anti-brute force activo |
| Vie | **Pruebas de seguridad funcionales** (intentar CSRF, fuerza bruta, subir PHP) | — | Confirmación de correcciones |

**Entregable Semana 2:** CRÍTICO-02 y CRÍTICO-05 corregidos. ALTO-03 y ALTO-04 corregidos.

---

### Semana 3 — Infraestructura de Seguridad

| Día | Tarea | Esfuerzo | Entregable |
|---|---|---|---|
| Lun–Mar | **F1.6** Configurar `.htaccess` con headers HTTP de seguridad y redirección HTTPS | Bajo | Headers de seguridad activos |
| Mié | **F1.6** Configurar cookies de sesión (`httponly`, `secure`, `samesite`) | Bajo | Cookies endurecidas |
| Jue | **F1.8** Limpiar `error_log()` con información sensible. Unificar `error_reporting` | Bajo | Logs limpios |
| Vie | **Auditoría de seguridad manual de Fase 1** + documentar cambios | — | Reporte de Fase 1 completado |

**Entregable Semana 3:** ALTO-02 (parcialmente), ALTO-05, ALTO-06 corregidos. **Sistema apto para pruebas con datos reales en entorno controlado.**

---

## FASE 2 — Semanas 4 a 7

### Semana 4 — Refactorización de Modelos

| Día | Tarea | Esfuerzo | Entregable |
|---|---|---|---|
| Lun–Mié | **F2.1** Refactorizar modelos para `return` en lugar de `echo` | Alto | Modelos con API limpia |
| Jue–Vie | **F2.2** Inicializar Composer, configurar PSR-4 autoloading, eliminar `include_once` | Bajo | Autoloading funcional |

**Entregable Semana 4:** Anti-patrón `ob_start` eliminado. Dependencias gestionadas con Composer.

---

### Semana 5 — Eliminar Duplicación de Código

| Día | Tarea | Esfuerzo | Entregable |
|---|---|---|---|
| Lun–Jue | **F2.3** Crear `BaseModel`, refactorizar los 4 modelos de usuario para extenderla | Alto | ~70% de duplicación eliminada |
| Vie | Pruebas de regresión completas | — | Funcionalidad intacta |

**Entregable Semana 5:** Codebase con bajo acoplamiento y sin duplicación crítica.

---

### Semana 6 — Configuración y Sesiones

| Día | Tarea | Esfuerzo | Entregable |
|---|---|---|---|
| Lun | **F2.5** Centralizar configuración, eliminar URLs hardcodeadas de JS | Bajo | Config centralizada |
| Mar–Mié | **F2.4** Front Controller + Router básico, mover fuente fuera del webroot | Alto | Arquitectura de carpetas segura |
| Jue | **F2.6** Implementar timeout de sesión con aviso al usuario | Bajo | Sesiones con expiración |
| Vie | Pruebas de regresión | — | — |

**Entregable Semana 6:** Arquitectura de carpetas correcta, sin acceso directo a controladores.

---

### Semana 7 — Logging y Observabilidad Básica

| Día | Tarea | Esfuerzo | Entregable |
|---|---|---|---|
| Lun–Mar | **F2.7** Instalar Monolog, configurar canales de log estructurados | Bajo | Logging profesional |
| Mié–Jue | Documentación técnica básica: README, diagrama de BD, guía de despliegue | Medio | Documentación inicial |
| Vie | Revisión general de Fase 2, limpieza de código | — | Reporte de Fase 2 |

**Entregable Semana 7:** **Sistema técnicamente sólido, apto para producción supervisada.**

---

## FASE 3 — Semanas 8 a 12

### Semana 8 — Testing Automatizado

| Tarea | Esfuerzo | Entregable |
|---|---|---|
| Instalar PHPUnit, configurar suite de tests | Bajo | Suite lista |
| Tests unitarios para modelos (crear, editar, obtener) | Alto | Cobertura de modelos |
| Tests de integración para login y recetas | Alto | Flujos críticos testados |

---

### Semana 9 — Containerización

| Tarea | Esfuerzo | Entregable |
|---|---|---|
| Dockerfile para PHP-FPM + Nginx | Medio | Imagen Docker funcional |
| docker-compose.yml con app + MySQL | Medio | Ambiente reproducible |
| Variables de entorno por ambiente (dev/staging/prod) | Bajo | Multi-ambiente configurado |

---

### Semana 10 — CI/CD

| Tarea | Esfuerzo | Entregable |
|---|---|---|
| GitHub Actions: lint + tests en cada PR | Bajo | Pipeline básico |
| Deploy automático a staging al merge en `main` | Medio | CD funcional |

---

### Semana 11 — Auditoría de Datos Médicos

| Tarea | Esfuerzo | Entregable |
|---|---|---|
| **F3.4** Tabla `audit_log`, triggers o middleware de auditoría | Alto | Trazabilidad de cambios |
| Implementar sistema de citas médicas completo | Alto | Feature completada |
| Estadísticas del dashboard con datos reales | Bajo | Dashboard correcto |

---

### Semana 12 — Monitoreo y Cierre

| Tarea | Esfuerzo | Entregable |
|---|---|---|
| Integrar Sentry (o similar) para error tracking | Bajo | Alertas en producción |
| Auditoría de seguridad final | Alto | Reporte de seguridad |
| Documentación final completa | Medio | Documentación lista para equipo |

**Entregable Semana 12:** **Sistema en producción, monitorado, testeado y con trazabilidad médica completa.**

---

## Resumen de Esfuerzo Total

| Fase | Semanas | Esfuerzo Estimado | Estado al Finalizar |
|---|---|---|---|
| Fase 1 — Seguridad Crítica | 1–3 | 3 semanas / 1 dev | Apto para pruebas controladas |
| Fase 2 — Refactorización | 4–7 | 4 semanas / 1 dev | Apto para producción supervisada |
| Fase 3 — Madurez Técnica | 8–12 | 5 semanas / 1 dev | Producción sostenible y auditada |
| **Total** | **12 semanas** | **~480 horas** | **Sistema production-ready** |
