<?php
include('conexion.php');

// Productos más vendidos
$mas_vendidos = $conn->query("
    SELECT p.nombre, SUM(iv.cantidad) AS ventas
    FROM items_venta iv
    JOIN productos p ON p.id = iv.producto_id
    GROUP BY p.id
    ORDER BY ventas DESC
    LIMIT 5
");
// Total clientes y turnos
$total_dueños = $conn->query("SELECT COUNT(*) AS total FROM dueños")->fetch_assoc()['total'];
$total_turnos = $conn->query("SELECT COUNT(*) AS total FROM turnos")->fetch_assoc()['total'];
$total_ventas = $conn->query("SELECT SUM(total) AS total FROM ventas")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes</title>
    <link rel="stylesheet" href="estilos.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <?php include('menu.php'); ?>
    <main class="container">
        <div class="card">
            <h2><i class="icon-report"></i> Reportes e Informes</h2>
            <div class="quick-stats">
                <div class="stat-card">
                    <h3>Total Dueños</h3>
                    <p><?=$total_dueños?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Turnos</h3>
                    <p><?=$total_turnos?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Ventas</h3>
                    <p>$<?=number_format($total_ventas, 2)?></p>
                </div>
            </div>
        </div>
        <div class="card">
            <h3>Productos Más Vendidos</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Unidades Vendidas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($r=$mas_vendidos->fetch_assoc()): ?>
                        <tr>
                            <td><?=htmlspecialchars($r['nombre'])?></td>
                            <td><?=$r['ventas']?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>

