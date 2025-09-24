<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mi Sistema MVC</title>

    <link rel="stylesheet" href="vistas/css/misestilos.css">
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <!-- Fuente Poppins -->
        <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome para iconos (sin integrity para evitar bloqueo si el hash cambia) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.6.1/css/responsive.dataTables.min.css">


<script src="https://cdn.datatables.net/responsive/2.6.1/js/dataTables.responsive.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

</head>

<body>
    <?php require 'vistas/includes/aside.php'; ?>

    <main>
        <header class="app-header">
            <div class="header-left">
                <img src="images/logo.png" alt="Logo" style="height:72px; max-width:260px; margin-left:8px;">
            </div>
            <div class="header-right">
                <a href="https://github.com/jfigueroah04/SistemaMVC" target="_blank" class="github-link" title="Mi Repositorio" style="background:#fff; color:#fff; border-radius:8px; padding:6px 16px; font-weight:600; display:inline-flex; align-items:center; gap:8px;">
                    <i class="fab fa-github fa-lg" style="color:#0d6efd;"></i> <span style="color:#fff;">Mi Repositorio</span>
                </a>
            </div>
        </header>

        <?php

        if (isset($vista)) {
            require "vistas/{$vista}.php";
        }
        ?>

    </main>


</body>


</html>