#!/usr/bin/env python3
"""
Envía correos de bienvenida corporativos a usuarios Biovital
Se conecta a la base de datos MySQL y obtiene los usuarios de todas las tablas:
- Médicos (registro_medico + login_medico)
- Pacientes (registro_paciente + login_paciente)
- Asistentes (registro_asistente + login_asistente)
- Administradores (registro_administrador + login_administrador)
"""

import ssl
import smtplib
import time
import sys
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
import mysql.connector
from mysql.connector import Error

# ── CONFIGURACIÓN SMTP ───────────────────────────────────────────────
SMTP_HOST      = "smtp.gmail.com"      # Cambiar según tu proveedor
SMTP_PORT      = 587                     # STARTTLS
SMTP_USER      = "tucorreo@biovital.com"  # TODO: Cambiar por correo real
SMTP_PASS      = "tu_password_app"        # TODO: Cambiar por contraseña de aplicación

# ── CONFIGURACIÓN BASE DE DATOS ─────────────────────────────────────
DB_CONFIG = {
    'host': 'localhost',
    'database': 'biovital',
    'user': 'root',
    'password': '',
    'port': 3306,
    'charset': 'utf8mb4'
}

# ── DESTINATARIOS EN COPIA ───────────────────────────────────────────
CC_ADDR  = "admin@biovital.com"      # Copia visible
BCC_ADDR = "soporte@biovital.com"    # Copia oculta

# ── ASUNTO DEL CORREO ────────────────────────────────────────────────
SUBJECT = "Bienvenido a Biovital - Tu aliado en Seguros Médicos"

# ── PLANTILLA CORPORATIVA EFUSIVA ────────────────────────────────────
TEMPLATE = """\
Estimado(a) {nombre} {apellidos},

¡Es un honor darle la bienvenida a Biovital!

En nombre de toda nuestra familia corporativa, nos complace enormemente contar con su presencia en nuestra plataforma de gestión de seguros médicos. Usted ha dado el primer paso hacia una experiencia transformadora en el cuidado de su salud y bienestar.

════════════════════════════════════════════════════════════════
🔐 SUS CREDENCIALES DE ACCESO
════════════════════════════════════════════════════════════════

Usuario:     {usuario}
Contraseña:  {password}
Correo:      {email}

Acceda a nuestra plataforma: https://biovital.com/login

════════════════════════════════════════════════════════════════

En Biovital no solo ofrecemos seguros médicos; construimos relaciones duraderas basadas en la confianza, la excelencia y el compromiso absoluto con su bienestar. Nuestra plataforma ha sido diseñada pensando en usted, con tecnología de punta que le permitirá:

✓ Gestionar sus pólizas y coberturas de forma integral
✓ Acceder a una red de médicos y especialistas de primer nivel
✓ Realizar trámites de reclamaciones de manera ágil y transparente
✓ Obtener atención personalizada las 24 horas del día
✓ Disfrutar de beneficios exclusivos por ser parte de nuestra familia

La salud es nuestro activo más preciado, y en Biovital entendemos que protegerla requiere más que un seguro: requiere un verdadero aliado que camine a su lado en cada momento.

🌟 Usted no es solo un cliente para nosotros; es un miembro valioso de la familia Biovital.

Le invitamos a explorar todas las funcionalidades de nuestra plataforma y a descubrir por qué miles de personas confían en nosotros para cuidar de lo más importante: su salud y la de sus seres queridos.

Si tiene alguna pregunta o necesita asistencia personalizada, nuestro equipo de soporte está a su disposición:

📧 Email: soporte@biovital.com
📞 Teléfono: +58 (XXX) XXX-XXXX
💬 Chat en vivo: Disponible en nuestra plataforma

Una vez más, bienvenido a bordo. Juntos construiremos un futuro más saludable y seguro.

Atentamente,

════════════════════════════════════════════════════════════════
El Equipo Directivo de Biovital
Seguros Médicos de Excelencia
"Protegiendo lo que más amas"
════════════════════════════════════════════════════════════════

Biovital S.A. | RIF: J-XXXXXXXX-X
Dirección: [Dirección corporativa]
Caracas, Venezuela

Este correo es confidencial y dirigido exclusivamente al destinatario.
Si lo ha recibido por error, por favor notifíquenos y elimínelo.
"""


def conectar_bd():
    """Establece conexión con la base de datos MySQL"""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        if conn.is_connected():
            print("✓ Conexión a base de datos establecida")
            return conn
    except Error as e:
        print(f"❌ Error conectando a BD: {e}")
        sys.exit(1)
    return None


def obtener_usuarios(conn):
    """Obtiene todos los usuarios de las diferentes tablas con sus credenciales"""
    usuarios = []
    cursor = conn.cursor(dictionary=True)
    
    # ── OBTENER MÉDICOS ────────────────────────────────────────────
    query_medicos = """
        SELECT 
            rm.id_medico as id,
            rm.nombre_medico as nombre,
            rm.apellido_medico as apellidos,
            rm.correo_medico as email,
            rm.cedula_medico as cedula,
            'medico' as tipo_usuario,
            lm.password_hash as password
        FROM registro_medico rm
        INNER JOIN login_medico lm ON rm.id_medico = lm.id_medico
        WHERE lm.status = 'activo'
    """
    cursor.execute(query_medicos)
    medicos = cursor.fetchall()
    for m in medicos:
        usuarios.append({
            'id': m['id'],
            'nombre': m['nombre'],
            'apellidos': m['apellidos'],
            'email': m['email'],
            'cedula': m['cedula'],
            'tipo': 'Médico',
            'usuario': m['cedula'],
            'password': m['password']
        })
    print(f"✓ {len(medicos)} médicos encontrados")
    
    # ── OBTENER PACIENTES ──────────────────────────────────────────
    query_pacientes = """
        SELECT 
            rp.id_paciente as id,
            rp.nombre_paciente as nombre,
            rp.apellido_paciente as apellidos,
            rp.correo_paciente as email,
            rp.cedula_paciente as cedula,
            'paciente' as tipo_usuario,
            lp.password_hash as password
        FROM registro_paciente rp
        INNER JOIN login_paciente lp ON rp.id_paciente = lp.id_paciente
        WHERE lp.status = 'activo'
    """
    cursor.execute(query_pacientes)
    pacientes = cursor.fetchall()
    for p in pacientes:
        usuarios.append({
            'id': p['id'],
            'nombre': p['nombre'],
            'apellidos': p['apellidos'],
            'email': p['email'],
            'cedula': p['cedula'],
            'tipo': 'Paciente',
            'usuario': p['cedula'],
            'password': p['password']
        })
    print(f"✓ {len(pacientes)} pacientes encontrados")
    
    # ── OBTENER ASISTENTES ─────────────────────────────────────────
    query_asistentes = """
        SELECT 
            ra.id_asistente as id,
            ra.nombre_asistente as nombre,
            ra.apellido_asistente as apellidos,
            ra.correo_asistente as email,
            ra.cedula_asistente as cedula,
            'asistente' as tipo_usuario,
            la.password_hash as password
        FROM registro_asistente ra
        INNER JOIN login_asistente la ON ra.id_asistente = la.id_asistente
        WHERE la.status = 'activo'
    """
    cursor.execute(query_asistentes)
    asistentes = cursor.fetchall()
    for a in asistentes:
        usuarios.append({
            'id': a['id'],
            'nombre': a['nombre'],
            'apellidos': a['apellidos'],
            'email': a['email'],
            'cedula': a['cedula'],
            'tipo': 'Asistente',
            'usuario': a['cedula'],
            'password': a['password']
        })
    print(f"✓ {len(asistentes)} asistentes encontrados")
    
    # ── OBTENER ADMINISTRADORES ────────────────────────────────────
    query_admin = """
        SELECT 
            rad.id_administrador as id,
            rad.nombre_administrador as nombre,
            rad.apellido_administrador as apellidos,
            rad.correo_administrador as email,
            rad.cedula_administrador as cedula,
            'administrador' as tipo_usuario,
            lad.password_hash as password
        FROM registro_administrador rad
        INNER JOIN login_administrador lad ON rad.id_administrador = lad.id_administrador
        WHERE lad.status = 'activo'
    """
    cursor.execute(query_admin)
    administradores = cursor.fetchall()
    for ad in administradores:
        usuarios.append({
            'id': ad['id'],
            'nombre': ad['nombre'],
            'apellidos': ad['apellidos'],
            'email': ad['email'],
            'cedula': ad['cedula'],
            'tipo': 'Administrador',
            'usuario': ad['cedula'],
            'password': ad['password']
        })
    print(f"✓ {len(administradores)} administradores encontrados")
    
    cursor.close()
    return usuarios


def enviar_correos(usuarios):
    """Envía correos a todos los usuarios obtenidos de la BD"""
    if not usuarios:
        print("❌ No hay usuarios para enviar correos")
        return
    
    print(f"\n📧 Iniciando envío de {len(usuarios)} correos...")
    print("=" * 60)
    
    context = ssl.create_default_context()
    exitosos = 0
    fallidos = 0
    
    try:
        with smtplib.SMTP(SMTP_HOST, SMTP_PORT, timeout=60) as server:
            server.starttls(context=context)
            server.login(SMTP_USER, SMTP_PASS)
            print("✓ Sesión SMTP iniciada\n")
            
            for idx, user in enumerate(usuarios, 1):
                try:
                    msg = MIMEMultipart()
                    msg["From"]    = f"Biovital Seguros Médicos <{SMTP_USER}>"
                    msg["To"]      = user['email']
                    msg["Cc"]      = CC_ADDR
                    msg["Bcc"]     = BCC_ADDR
                    msg["Subject"] = SUBJECT
                    
                    # Preparar datos para la plantilla
                    datos = {
                        'nombre': user['nombre'],
                        'apellidos': user['apellidos'],
                        'email': user['email'],
                        'usuario': user['usuario'],
                        'password': user['password']
                    }
                    
                    cuerpo = TEMPLATE.format(**datos)
                    msg.attach(MIMEText(cuerpo, "plain", "utf-8"))
                    
                    server.send_message(msg)
                    exitosos += 1
                    print(f"✔ [{idx}/{len(usuarios)}] {user['tipo']}: {user['nombre']} {user['apellidos']} - {user['email']}")
                    
                    # Pausa entre envíos para no saturar el servidor
                    time.sleep(2)
                    
                except Exception as e:
                    fallidos += 1
                    print(f"✗ [{idx}/{len(usuarios)}] Error enviando a {user['email']}: {str(e)[:50]}")
                    continue
                
    except Exception as e:
        print(f"\n❌ Error en conexión SMTP: {e}")
        return
    
    # Resumen final
    print("\n" + "=" * 60)
    print("RESUMEN DE ENVÍO")
    print("=" * 60)
    print(f"✓ Enviados exitosamente: {exitosos}")
    print(f"✗ Fallidos: {fallidos}")
    print(f"📊 Total procesados: {len(usuarios)}")


def main():
    """Función principal"""
    print("=" * 60)
    print("   BIOVITAL - SISTEMA DE ENVÍO DE CORREOS CORPORATIVOS")
    print("   Seguros Médicos de Excelencia")
    print("=" * 60)
    print()
    
    # 1. Conectar a base de datos
    conn = conectar_bd()
    if not conn:
        return
    
    # 2. Obtener usuarios
    print("\n📋 Obteniendo usuarios de la base de datos...")
    usuarios = obtener_usuarios(conn)
    conn.close()
    print(f"\n✓ Total usuarios activos: {len(usuarios)}")
    
    if usuarios:
        # 3. Enviar correos
        enviar_correos(usuarios)
    else:
        print("\n❌ No se encontraron usuarios activos en la base de datos")
    
    print("\n" + "=" * 60)
    print("   PROCESO COMPLETADO")
    print("=" * 60)


if __name__ == '__main__':
    main()
