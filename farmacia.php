<?php
include('conexion.php');

// Agregar producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $buy = floatval($_POST['buy']);
    $sell = floatval($_POST['sell']);
    $qty = intval($_POST['qty']);
    $expiry = $conn->real_escape_string($_POST['expiry'])?:NULL;
    $category = $conn->real_escape_string($_POST['category']);
    $conn->query("INSERT INTO productos (nombre, cantidad, precio_compra, precio_venta, vencimiento, categoria) VALUES ('$name', $qty, $buy, $sell, ".($expiry? "'$expiry'":"NULL").", '$category')");
    header('Location: farmacia.php');
    exit;
}

// Registrar entrada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['entry'])) {
    $pid = intval($_POST['product_id']);
    $qty = intval($_POST['entry_qty']);
    $expiry = $conn->real_escape_string($_POST['entry_expiry'])?:NULL;
    $nota = $conn->real_escape_string($_POST['nota']);
    $conn->query("UPDATE productos SET cantidad = cantidad + $qty, vencimiento = ".($expiry? "'$expiry'":"vencimiento")." WHERE id = $pid");
    $conn->query("INSERT INTO entradas_productos (producto_id, cantidad, fecha, nota) VALUES ($pid, $qty, NOW(), '$nota')");
    header('Location: farmacia.php');
    exit;
}

// Listado productos
$products = $conn->query("SELECT * FROM productos ORDER BY nombre ASC");
$entries = $conn->query("SELECT pe.*, p.nombre FROM entradas_productos pe JOIN productos p ON p.id = pe.producto_id ORDER BY pe.fecha DESC LIMIT 30");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmacia</title>
    <link rel="stylesheet" href="estilos.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <?php include('menu.php'); ?>
    <main class="container">
        <div class="card">
            <h2><i class="icon-pharmacy"></i> Farmacia - Inventario</h2>
            <div class="card-form">
                <h3>Agregar Producto Nuevo</h3>
                <form method="post">
                    <input type="hidden" name="add_product" value="1">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Precio de compra</label>
                        <input type="number" step="0.01" name="buy" required>
                    </div>
                    <div class="form-group">
                        <label>Precio de venta</label>
                        <input type="number" step="0.01" name="sell" required>
                    </div>
                    <div class="form-group">
                        <label>Cantidad inicial</label>
                        <input type="number" name="qty" value="0" required>
                    </div>
                    <div class="form-group">
                        <label>Vencimiento (opcional)</label>
                        <input type="date" name="expiry">
                    </div>
                    <div class="form-group">
                        <label>Categoría</label>
                        <input type="text" name="category" placeholder="Ej: Medicamentos, Alimentos, Accesorios">
                    </div>
                    <button type="submit" class="btn-primary">Agregar Producto</button>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-form">
                <h3>Registrar Entrada de Stock</h3>
                <form method="post">
                    <input type="hidden" name="entry" value="1">
                    <div class="form-group">
                        <label>Producto</label>
                        <select name="product_id" required>
                            <?php
                            $products->data_seek(0);
                            while($p = $products->fetch_assoc()): ?>
                            <option value="<?=$p['id']?>"><?=htmlspecialchars($p['nombre'])?> (<?=$p['cantidad']?>u)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Cantidad a ingresar</label>
                        <input type="number" name="entry_qty" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Vencimiento del lote (opcional)</label>
                        <input type="date" name="entry_expiry">
                    </div>
                    <div class="form-group">
                        <label>Nota / Lote</label>
                        <input type="text" name="nota" placeholder="Ej: Lote #123 Proveedor X">
                    </div>
                    <button type="submit" class="btn-primary">Registrar Entrada</button>
                </form>
            </div>
        </div>
        <div class="card">
            <h3>Productos en Stock</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Compra</th>
                            <th>Venta</th>
                            <th>Vencimiento</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $products->data_seek(0);
                        while($p = $products->fetch_assoc()):
                            $estado = '';
                            if ($p['cantidad'] <= CRITICAL_THRESHOLD) $estado = '<span class="status critical">CRÍTICO</span>';
                            elseif ($p['cantidad'] <= ALERT_THRESHOLD) $estado = '<span class="status warning">Poco stock</span>';
                            $venc = $p['vencimiento'];
                            if ($venc) {
                                $diff = (strtotime($venc) - time())/(60*60*24);
                                if ($diff <= 60 && $diff >= 0) $estado .= ' <span class="status warning">Vence en '.intval($diff).' días</span>';
                                if ($diff < 0) $estado .= ' <span class="status critical">Vencido</span>';
                            }
                        ?>
                        <tr>
                            <td><?=htmlspecialchars($p['nombre'])?></td>
                            <td><?=$p['cantidad']?></td>
                            <td>$<?=number_format($p['precio_compra'],2)?></td>
                            <td>$<?=number_format($p['precio_venta'],2)?></td>
                            <td><?= $p['vencimiento'] ?: '—' ?></td>
                            <td><?= $estado ?: '<span class="status ok">OK</span>' ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card">
            <h3>Entradas Recientes</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Nota</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($e = $entries->fetch_assoc()): ?>
                        <tr>
                            <td><?=$e['fecha']?></td>
                            <td><?=htmlspecialchars($e['nombre'])?></td>
                            <td><?=$e['cantidad']?></td>
                            <td><?=htmlspecialchars($e['nota'])?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
