<?php
include('conexion.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veterinaria Dr. Martín</title>
    <link rel="stylesheet" href="estilos.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="logo">
            <img src="logo.png" alt="Vetrix">
        </div>
        <div class="header-info">
            <h1>Vetrix</h1>
            <p>Sistema de gestión clínica y farmacia</p>
        </div>
    </header>
    <?php include('menu.php'); ?>
    <main class="dashboard">
        <div class="welcome-card">
            <h2>Bienvenido al Sistema de Gestión</h2>
            <p>Usá el menú superior para navegar entre las diferentes secciones del sistema.</p>
        </div>
        <div class="quick-stats">
            <?php
            $total_mascotas = $conn->query("SELECT COUNT(*) as total FROM mascotas")->fetch_assoc()['total'];
            $total_productos = $conn->query("SELECT COUNT(*) as total FROM productos")->fetch_assoc()['total'];
            $turnos_hoy = $conn->query("SELECT COUNT(*) as total FROM turnos WHERE fecha = CURDATE()")->fetch_assoc()['total'];
            ?>
            <div class="stat-card">
                <h3>Mascotas Registradas</h3>
                <p><?=$total_mascotas?></p>
            </div>
            <div class="stat-card">
                <h3>Productos en Stock</h3>
                <p><?=$total_productos?></p>
            </div>
            <div class="stat-card">
                <h3>Turnos Hoy</h3>
                <p><?=$turnos_hoy?></p>
            </div>
        </div>
    </main>
    <footer>
        <p>© 2025 Veterinaria Dr. Martín — Todos los derechos reservados</p>
    </footer>
</body>
</html>
