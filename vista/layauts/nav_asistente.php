<meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="../../css/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="../../css/adminlte.min.css">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
    </ul>
    <ul class="navbar-nav ml-auto">
      <a href="../../controlador/logout.php">Cerrar sesión</a>
    </ul>
  </nav>

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="../asistente/asi_catalogo.php" class="brand-link">
      <img src="../../img/logo_azul.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">BIOVITAL</span>
    </a>

    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img id="avatar4" src="../../img/avatar.png" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block"><?php echo htmlspecialchars($_SESSION['nombre_us'] ?? '', ENT_QUOTES, 'UTF-8'); ?></a>
        </div>
      </div>

      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          <li class="nav-header">Usuario</li>
          <li class="nav-item">
            <a href="../asistente/asi_editar_datos.php" class="nav-link">
              <i class="nav-icon fas fa-user-cog"></i>
              <p>Datos personales</p>
            </a>
          </li>
          <li class="nav-header">Clínica</li>
          <li class="nav-item">
            <a href="../asistente/adm_recetas.php" class="nav-link">
              <i class="nav-icon fas fa-prescription-bottle-alt"></i>
              <p>Recetas</p>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </aside>
