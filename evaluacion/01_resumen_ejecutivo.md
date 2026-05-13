# 01 — Resumen Ejecutivo

**Sistema:** Biovital  
**Sector:** Salud / Médico  
**Stack:** PHP 7/8 · MySQL · PDO · Bootstrap 4 · jQuery · AdminLTE  
**Arquitectura declarada:** MVC (Modelo-Vista-Controlador)  
**Fecha de evaluación:** 2026-05-13  
**Evaluador:** Auditoría técnica automatizada (arquitecto senior)

---

## Veredicto General

> **⛔ NO APTO PARA PRODUCCIÓN**

El sistema Biovital presenta **vulnerabilidades críticas de seguridad** que lo hacen completamente inadecuado para manejar información médica en un entorno de producción real. El problema más grave —y no negociable— es que **las contraseñas de todos los usuarios se almacenan en texto plano** en la base de datos. Esto, en un sistema del sector salud que maneja datos sensibles de pacientes, médicos y recetas médicas, constituye una falla de diseño de nivel crítico.

Adicionalmente, el sistema carece de protección CSRF, no implementa control de autorización en la mayoría de sus endpoints, tiene vectores de XSS activos en las vistas, acepta cargas de archivos sin validación real, y expone credenciales de base de datos hardcodeadas con usuario `root` sin contraseña.

---

## Estado del Sistema por Dimensión

| Dimensión | Estado | Nivel de Riesgo |
|---|---|---|
| Autenticación | ❌ Crítico | Las contraseñas se almacenan en texto plano |
| Autorización | ❌ Crítico | Los endpoints no verifican sesión ni pertenencia de recursos |
| Cifrado de datos | ❌ Crítico | Sin cifrado en tránsito ni en reposo |
| Protección CSRF | ❌ Ausente | Ningún formulario o endpoint tiene token CSRF |
| Protección XSS | ❌ Ausente | No hay sanitización de salida en vistas |
| Subida de archivos | 🔴 Alto | Validación basada en MIME falsificable del cliente |
| Arquitectura MVC | 🟡 Parcial | Patrón presente pero inconsistente y sin capas de servicio |
| Calidad de código | 🟡 Media | Mezcla de estilos, anti-patrones, código de depuración en producción |
| Base de datos | 🔴 Alto | Credenciales hardcodeadas, usuario root sin contraseña |
| Testing | ❌ Ausente | Cero pruebas automatizadas |
| Documentación | 🟡 Parcial | README presente solo en carpeta `ejemplo/` |
| Monitoreo | ❌ Ausente | Sin sistema de monitoreo, logging estructurado ni alertas |
| Infraestructura | ❌ Sin definir | Sin Docker, sin CI/CD, sin configuración de servidor |

---

## Riesgos Principales

### Riesgo de Negocio
- Exposición de datos médicos de pacientes (HIPAA/LOPD equivalent) implica **responsabilidad legal directa**.
- La filtración de contraseñas en texto plano puede comprometer cuentas de usuarios en otros servicios (credential stuffing).
- El repositorio está públicamente expuesto en GitHub con toda la lógica del sistema.
- Datos de prueba con **imágenes de personas reales** (figuras políticas internacionales) almacenados en producción.

### Riesgo Técnico
- Un atacante no autenticado puede llamar directamente a los controladores PHP y obtener datos de pacientes.
- La carpeta `ejemplo/` contiene un script Python que **lee contraseñas en texto plano** de la base de datos y las envía por correo — confirmando que las contraseñas nunca fueron hasheadas.
- Sin mecanismo de sesión seguro, cualquier usuario podría suplantar a otro modificando el ID de sesión.
- La subida de avatares no valida el contenido real del archivo, permitiendo potencialmente subir PHP malicioso.

### Riesgo Operativo
- Sin sistema de backups definido.
- Sin variables de entorno: un cambio de ambiente requiere editar código fuente.
- Las estadísticas del panel de administración están **hardcodeadas** en JavaScript (`$('#total_usuarios').text('4')`), no son datos reales.
- La función "próximas citas" devuelve siempre 0 — feature incompleta expuesta al usuario.

---

## Conclusión Ejecutiva

Biovital es un proyecto con **intención arquitectónica correcta** (MVC, PDO, separación de roles), pero con una implementación que presenta fallas fundamentales de seguridad. El sistema puede servir como base para construir un producto seguro, pero requiere una refactorización profunda antes de manejar datos reales de pacientes.

**Estimación de esfuerzo para llevarlo a producción segura:** 6–10 semanas de trabajo técnico focalizado, con prioridad absoluta en seguridad.
