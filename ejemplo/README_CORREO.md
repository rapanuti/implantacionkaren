# 📧 Sistema de Envío de Correos Biovital

Script Python para enviar correos de bienvenida corporativos a todos los usuarios registrados en la base de datos Biovital.

## 🎯 Características

- **Conexión directa a MySQL**: Obtiene usuarios de las tablas de registro (médicos, pacientes, asistentes, administradores)
- **Texto corporativo efusivo**: Mensaje profesional de bienvenida para empresa de seguros médicos
- **Incluye credenciales**: Usuario (cédula) y contraseña en cada correo
- **Soporte multi-rol**: Envía a médicos, pacientes, asistentes y administradores
- **Control de copias**: CC visible y BCC oculta configurables

## ⚙️ Configuración Requerida

Antes de ejecutar, edita estas variables en `correo_biovital.py`:

```python
# ── CONFIGURACIÓN SMTP ─────────────────────────────────────
SMTP_HOST      = "smtp.gmail.com"          # Servidor SMTP
SMTP_USER      = "tucorreo@biovital.com"   # Tu correo
SMTP_PASS      = "tu_password_app"         # Contraseña de aplicación

# ── DESTINATARIOS EN COPIA ───────────────────────────────────
CC_ADDR  = "admin@biovital.com"           # Copia visible
BCC_ADDR = "soporte@biovital.com"         # Copia oculta
```

### Configuración de Gmail (ejemplo)

1. Habilitar [Verificación en 2 pasos](https://myaccount.google.com/security)
2. Generar [Contraseña de aplicación](https://myaccount.google.com/apppasswords)
3. Usar esa contraseña en `SMTP_PASS`

## 🚀 Uso

### 1. Instalar dependencia
```bash
pip install mysql-connector-python
```

### 2. Ejecutar script
```bash
cd c:\xampp\htdocs\biovital\ejemplo
python correo_biovital.py
```

## 📊 Salida Esperada

```
============================================================
   BIOVITAL - SISTEMA DE ENVÍO DE CORREOS CORPORATIVOS
============================================================

✓ Conexión a base de datos establecida

📋 Obteniendo usuarios de la base de datos...
✓ 15 médicos encontrados
✓ 42 pacientes encontrados
✓ 8 asistentes encontrados
✓ 3 administradores encontrados

✓ Total usuarios activos: 68

📧 Iniciando envío de 68 correos...
============================================================
✓ Sesión SMTP iniciada

✔ [1/68] Médico: Juan Pérez - jperez@email.com
✔ [2/68] Paciente: María García - mgarcia@email.com
...

============================================================
RESUMEN DE ENVÍO
============================================================
✓ Enviados exitosamente: 68
✗ Fallidos: 0
📊 Total procesados: 68
```

## 📝 Plantilla de Correo

El correo incluye:
- **Saludo personalizado** con nombre y apellidos
- **Credenciales de acceso**: Usuario (cédula) y contraseña
- **Enlace a la plataforma**: https://biovital.com/login
- **Mensaje corporativo efusivo** destacando:
  - Bienvenida a la familia Biovital
  - Beneficios de la plataforma
  - Red de médicos especialistas
  - Atención 24/7
  - Contacto de soporte

## 🔧 Estructura de la Base de Datos

El script consulta estas tablas:

| Tipo | Tabla Registro | Tabla Login |
|------|---------------|-------------|
| Médico | `registro_medico` | `login_medico` |
| Paciente | `registro_paciente` | `login_paciente` |
| Asistente | `registro_asistente` | `login_asistente` |
| Administrador | `registro_administrador` | `login_administrador` |

## ⚠️ Notas Importantes

- **Pausa entre envíos**: 2 segundos entre cada correo para no saturar el servidor SMTP
- **Solo usuarios activos**: Filtra por `status = 'activo'` en las tablas de login
- **Codificación UTF-8**: Soporta caracteres especiales (tildes, ñ, etc.)
- **Copias**: Todos los correos incluyen CC y BCC configurados

## 🆘 Solución de Problemas

### Error de conexión SMTP
```
❌ Error en conexión SMTP: (535, b'5.7.8 Username and Password not accepted')
```
→ Verifica usuario y contraseña. Para Gmail usa "Contraseña de aplicación"

### Error de base de datos
```
❌ Error conectando a BD: 2003 (HY000): Can't connect to MySQL server
```
→ Verifica que XAMPP esté corriendo (Apache + MySQL)

### No se encuentran usuarios
```
❌ No se encontraron usuarios activos en la base de datos
```
→ Verifica que existan registros con `status = 'activo'` en las tablas de login

## 📞 Soporte

Para asistencia técnica contactar a:
- 📧 Email: soporte@biovital.com
- 🌐 Portal: https://biovital.com/soporte
