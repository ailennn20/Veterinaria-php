<?php
include('conexion.php');

// Agregar turno
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_turno'])) {
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $motivo = $conn->real_escape_string($_POST['motivo']);
    $id_mascota = (int)$_POST['id_mascota'];
    $conn->query("INSERT INTO turnos (fecha, hora, mascota_id, motivo) VALUES ('$fecha', '$hora', $id_mascota, '$motivo')");
    header("Location: agenda.php");
    exit;
}

// Cancelar turno
if (isset($_GET['cancelar'])) {
    $id = (int)$_GET['cancelar'];
    $conn->query("UPDATE turnos SET estado='cancelado' WHERE id=$id");
    header("Location: agenda.php");
    exit;
}

// Obtener datos
$mascotas = $conn->query("SELECT id, nombre FROM mascotas");
$turnos = $conn->query("SELECT t.*, m.nombre AS mascota FROM turnos t LEFT JOIN mascotas m ON t.mascota_id=m.id ORDER BY t.fecha, t.hora");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda de Turnos</title>
    <link rel="stylesheet" href="estilos.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <?php include('menu.php'); ?>
    <main class="container">
        <div class="card">
            <h2><i class="icon-calendar"></i> Agenda de Turnos</h2>
            <div class="card-form">
                <h3>Agregar Nuevo Turno</h3>
                <form method="post">
                    <input type="hidden" name="add_turno" value="1">
                    <div class="form-group">
                        <label>Fecha</label>
                        <input type="date" name="fecha" required>
                    </div>
                    <div class="form-group">
                        <label>Hora</label>
                        <input type="time" name="hora" required>
                    </div>
                    <div class="form-group">
                        <label>Mascota</label>
                        <select name="id_mascota" required>
                            <?php while($m=$mascotas->fetch_assoc()): ?>
                            <option value="<?=$m['id']?>"><?=htmlspecialchars($m['nombre'])?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Motivo</label>
                        <textarea name="motivo" rows="2" required></textarea>
                    </div>
                    <button type="submit" class="btn-primary">Guardar Turno</button>
                </form>
            </div>
        </div>
        <div class="card">
            <h3>Turnos Programados</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Mascota</th>
                            <th>Motivo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($t=$turnos->fetch_assoc()): ?>
                        <tr>
                            <td><?=$t['fecha']?></td>
                            <td><?=$t['hora']?></td>
                            <td><?=htmlspecialchars($t['mascota'])?></td>
                            <td><?=htmlspecialchars($t['motivo'])?></td>
                            <td><span class="status <?=$t['estado']?>"><?=ucfirst($t['estado'])?></span></td>
                            <td>
                                <?php if($t['estado']=='activo'): ?>
                                <a href="agenda.php?cancelar=<?=$t['id']?>" class="btn-danger">Cancelar</a>
                                <?php else: ?>
                                <span>—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
