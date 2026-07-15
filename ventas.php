<?php
include('conexion.php');

// Registrar venta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_sale'])) {
    $id_mascota = isset($_POST['id_mascota']) ? (int)$_POST['id_mascota'] : NULL;
    $id_producto = (int)$_POST['id_producto'];
    $cantidad = (int)$_POST['cantidad'];
    // Obtener precio del producto
    $p = $conn->query("SELECT precio_venta, cantidad FROM productos WHERE id=$id_producto")->fetch_assoc();
    $total = $p['precio_venta'] * $cantidad;
    // Insertar venta
    $conn->query("INSERT INTO ventas (mascota_id, total) VALUES ($id_mascota, $total)");
    $sale_id = $conn->insert_id;
    // Insertar item de venta
    $conn->query("INSERT INTO items_venta (venta_id, producto_id, cantidad, precio_unitario) VALUES ($sale_id, $id_producto, $cantidad, ".$p['precio_venta'].")");
    // Descontar del stock
    $nuevoStock = $p['cantidad'] - $cantidad;
    $conn->query("UPDATE productos SET cantidad=$nuevoStock WHERE id=$id_producto");
    header('Location: ventas.php');
    exit;
}

// Obtener datos
$mascotas = $conn->query("SELECT id, nombre FROM mascotas");
$productos = $conn->query("SELECT id, nombre, precio_venta, cantidad FROM productos");
$ventas = $conn->query("SELECT v.id, m.nombre AS mascota, v.total, v.fecha, v.metodo_pago
                        FROM ventas v LEFT JOIN mascotas m ON v.mascota_id=m.id
                        ORDER BY v.fecha DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas y Caja</title>
    <link rel="stylesheet" href="estilos.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <?php include('menu.php'); ?>
    <main class="container">
        <div class="card">
            <h2><i class="icon-sales"></i> Ventas y Caja</h2>
            <div class="card-form">
                <h3>Registrar Venta</h3>
                <form method="post">
                    <input type="hidden" name="add_sale" value="1">
                    <div class="form-group">
                        <label>Mascota (opcional)</label>
                        <select name="id_mascota">
                            <option value="">-- Sin asociar --</option>
                            <?php while($m=$mascotas->fetch_assoc()): ?>
                            <option value="<?=$m['id']?>"><?=htmlspecialchars($m['nombre'])?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Producto</label>
                        <select name="id_producto" required>
                            <?php while($p=$productos->fetch_assoc()): ?>
                            <option value="<?=$p['id']?>"><?=htmlspecialchars($p['nombre'])?> (Stock: <?=$p['cantidad']?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Cantidad</label>
                        <input type="number" name="cantidad" required min="1">
                    </div>
                    <button type="submit" class="btn-primary">Registrar Venta</button>
                </form>
            </div>
        </div>
        <div class="card">
            <h3>Historial de Ventas</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Mascota</th>
                            <th>Total</th>
                            <th>Fecha</th>
                            <th>Método de Pago</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($v=$ventas->fetch_assoc()): ?>
                        <tr>
                            <td><?=$v['id']?></td>
                            <td><?=htmlspecialchars($v['mascota'])?:'—'?></td>
                            <td>$<?=number_format($v['total'],2)?></td>
                            <td><?=$v['fecha']?></td>
                            <td><?=$v['metodo_pago']?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
