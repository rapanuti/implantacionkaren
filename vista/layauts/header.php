<?php
/**
 * [SECURITY FIX] 2026-05-13 - PROBLEMA-04, PROBLEMA-06
 *   D2/D3: CSRF token expuesto en meta tag para uso en peticiones AJAX.
 *   Se agrega Subresource Integrity (SRI) a recursos de CDN externos.
 *   El middleware ya inició la sesión de forma segura antes de este punto.
 */
// El middleware ya fue llamado por el controlador que incluyó esta vista.
// Llamamos generateCsrfToken() para asegurar que el token exista.
if (function_exists('generateCsrfToken')) {
    $csrf = generateCsrfToken();
} else {
    // Fallback: si el header se carga desde una vista sin middleware
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    $csrf = $_SESSION['csrf_token'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- [SECURITY FIX] D3: Token CSRF en meta tag para peticiones AJAX -->
  <meta name="csrf-token" content="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

  <!-- jQuery con Subresource Integrity -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"
          integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
          crossorigin="anonymous"></script>

  <!-- Bootstrap CSS con SRI -->
  <link rel="stylesheet"
        href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"
        integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z"
        crossorigin="anonymous">

  <!-- Font Awesome (local) -->
  <link rel="stylesheet" href="../../css/css/all.min.css">

  <!-- AdminLTE CSS (local) -->
  <link rel="stylesheet" href="../../css/adminlte.min.css">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

  <!-- [SECURITY FIX] D3: Configurar jQuery para enviar CSRF en todas las peticiones AJAX -->
  <script>
  (function() {
    // Leer token desde el meta tag — nunca hardcodeado
    var csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken && typeof $ !== 'undefined') {
      $.ajaxSetup({
        beforeSend: function(xhr) {
          xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken.getAttribute('content'));
        }
      });
    }
    // Re-aplicar cuando jQuery cargue si aún no estaba disponible
    document.addEventListener('DOMContentLoaded', function() {
      var token = document.querySelector('meta[name="csrf-token"]');
      if (token && typeof $ !== 'undefined') {
        $.ajaxSetup({
          beforeSend: function(xhr) {
            xhr.setRequestHeader('X-CSRF-TOKEN', token.getAttribute('content'));
          }
        });
      }
    });
  })();
  </script>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
