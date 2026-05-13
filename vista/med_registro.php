<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Médico - BioVital</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 50px 0;
        }
        .registro-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .registro-header {
            background: #4e73df;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .registro-header h2 {
            margin: 0;
            font-size: 28px;
        }
        .registro-body {
            padding: 40px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .form-group.required label:after {
            content: " *";
            color: red;
        }
        .btn-registro {
            background: #4e73df;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            margin-top: 20px;
        }
        .btn-registro:hover {
            background: #2e59d9;
        }
        .alert {
            margin-top: 20px;
            display: none;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #4e73df;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="registro-container">
            <div class="registro-header">
                <h2><i class="fas fa-user-md"></i> Registro de Médico</h2>
                <p>Complete todos los campos para registrarse como médico</p>
            </div>
            <div class="registro-body">
                <form id="form-registro" method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group required">
                                <label for="nombre">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group required">
                                <label for="apellidos">Apellido</label>
                                <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group required">
                                <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group required">
                                <label for="cedula">Cédula</label>
                                <input type="text" class="form-control" id="cedula" name="cedula" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group required">
                                <label for="telefono">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group required">
                                <label for="sexo">Sexo</label>
                                <select class="form-control" id="sexo" name="sexo" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group required">
                        <label for="direccion">Dirección</label>
                        <input type="text" class="form-control" id="direccion" name="direccion" required>
                    </div>
                    
                    <div class="form-group required">
                        <label for="correo">Correo Electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="adicional">Información Adicional</label>
                        <textarea class="form-control" id="adicional" name="adicional" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group required">
                                <label for="pass">Contraseña</label>
                                <input type="password" class="form-control" id="pass" name="pass" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group required">
                                <label for="confirm_pass">Confirmar Contraseña</label>
                                <input type="password" class="form-control" id="confirm_pass" name="confirm_pass" required>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-registro">
                        <i class="fas fa-check-circle"></i> Crear Cuenta
                    </button>
                </form>
                
                <div id="alert-success" class="alert alert-success" style="display:none;">
                    <i class="fas fa-check-circle"></i> <span id="success-message"></span>
                </div>
                <div id="alert-error" class="alert alert-danger" style="display:none;">
                    <i class="fas fa-exclamation-circle"></i> <span id="error-message"></span>
                </div>
                
                <div class="login-link">
                    <a href="login_medico.php"><i class="fas fa-sign-in-alt"></i> ¿Ya tienes cuenta? Inicia sesión aquí</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/registro_medico.js"></script>
</body>
</html>