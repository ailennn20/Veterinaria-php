<nav class="main-menu">
    <ul>
        <li><a href="index.php" <?php if(basename($_SERVER['PHP_SELF']) == 'index.php') echo 'class="active"'; ?>>Inicio</a></li>
        <li><a href="mascotas.php" <?php if(basename($_SERVER['PHP_SELF']) == 'mascotas.php') echo 'class="active"'; ?>>Mascotas</a></li>
        <li><a href="farmacia.php" <?php if(basename($_SERVER['PHP_SELF']) == 'farmacia.php') echo 'class="active"'; ?>>Farmacia</a></li>
        <li><a href="ventas.php" <?php if(basename($_SERVER['PHP_SELF']) == 'ventas.php') echo 'class="active"'; ?>>Ventas</a></li>
        <li><a href="agenda.php" <?php if(basename($_SERVER['PHP_SELF']) == 'agenda.php') echo 'class="active"'; ?>>Agenda</a></li>
        <li><a href="reportes.php" <?php if(basename($_SERVER['PHP_SELF']) == 'reportes.php') echo 'class="active"'; ?>>Reportes</a></li>
    </ul>
</nav>
