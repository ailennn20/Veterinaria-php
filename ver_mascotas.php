<?php
include('conexion.php');
$pet = null;
$visits = [];

// Buscar mascota por ID o nombre
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $res = $conn->query("SELECT p.*, o.nombre as owner_name, o.telefono as owner_phone FROM mascotas p JOIN dueños o ON p.dueño_id = o.id WHERE p.id = $id");
    $pet = $res->fetch_assoc();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_name'])) {
    $name = $conn->real_escape_string($_POST['search_name']);
    $res = $conn->query("SELECT p.*, o.nombre as owner_name, o.telefono as owner_phone FROM mascotas p JOIN dueños o ON p.dueño_id = o.id WHERE p.nombre LIKE '%$name%' LIMIT 1");
    $pet = $res->fetch_assoc();
}

// Agregar visita clínica
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_visit') {
    $pet_id = (int)$_POST['pet_id'];
    $fecha = $conn->real_escape_string($_POST['fecha']);
    $motivo = $conn->real_escape_string($_POST['motivo']);
    $diagnostico = $conn->real_escape_string($_POST['diagnostico']);
    $tratamiento = $conn->real_escape_string($_POST['tratamiento']);
    $peso = floatval($_POST['peso']);
    $conn->query("INSERT INTO historias_clinicas (mascota_id, fecha, motivo_consulta, diagnostico, tratamiento, peso) VALUES ($pet_id, '$fecha', '$motivo', '$diagnostico', '$tratamiento', $peso)");
    $conn->query("UPDATE mascotas SET peso = $peso WHERE id = $pet_id");
    header("Location: ver_mascota.php?id=$pet_id");
    exit;
}

// Obtener historial clínico
if ($pet) {
    $pid = (int)$pet['id'];
    $vres = $conn->query("SELECT * FROM historias_clinicas WHERE mascota_id = $pid ORDER BY fecha DESC");
    while ($r = $vres->fetch_assoc()) {
        $visits[] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha Mascota</title>
    <link rel="stylesheet" href="estilos.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="logo">
            <img src="logo.png" alt="Logo Veterinaria Dr. Martín">
        </div>
        <div class="header-info">
            <h1>Veterinaria Dr. Martín</h1>
            <p>Sistema de gestión clínica y farmacia</p>
        </div>
    </header>
    <?php include('menu.php'); ?>
    <main class="container">
        <div class="card">
            <h2><i class="icon-pet"></i> Ficha de Mascota</h2>
            <form method="post" class="search-form">
                <input type="text" name="search_name" placeholder="Buscar mascota por nombre (ej: Max)" required>
                <button type="submit" class="btn-primary">Buscar</button>
            </form>
        </div>
        <?php if (!$pet): ?>
            <div class="card">
                <p>No se encontró la mascota. Intenta buscar por nombre o selecciona una desde el listado.</p>
                <a href="mascotas.php" class="btn-secondary">Ver listado de mascotas</a>
            </div>
        <?php else: ?>
            <div class="card">
                <h3><?= htmlspecialchars($pet['nombre']) ?> — <?= htmlspecialchars($pet['raza']) ?></h3>
                <div class="pet-info">
                    <div>
                        <p><strong>Dueño:</strong> <?= htmlspecialchars($pet['owner_name']) ?></p>
                        <p><strong>Teléfono:</strong> <?= htmlspecialchars($pet['owner_phone']) ?></p>
                        <p><strong>Especie:</strong> <?= htmlspecialchars($pet['especie']) ?></p>
                        <p><strong>Peso:</strong> <?= htmlspecialchars($pet['peso']) ?> kg</p>
                        <p><strong>Alergias:</strong> <?= htmlspecialchars($pet['alergias']) ?: 'Ninguna' ?></p>
                    </div>
                    <div class="pet-actions">
                        <a href="ver_mascota.php?id=<?= $pet['id'] ?>" class="btn-info">Refrescar</a>
                        <a href="mascotas.php" class="btn-secondary">Volver al listado</a>
                    </div>
                </div>
            </div>
            <div class="card">
                <h3>Agregar Consulta Clínica</h3>
                <form method="post" class="clinical-form">
                    <input type="hidden" name="action" value="add_visit">
                    <input type="hidden" name="pet_id" value="<?= $pet['id'] ?>">
                    <div class="form-group">
                        <label>Fecha</label>
                        <input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Motivo</label>
                        <input type="text" name="motivo" required>
                    </div>
                    <div class="form-group">
                        <label>Diagnóstico</label>
                        <textarea name="diagnostico" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Tratamiento</label>
                        <textarea name="tratamiento" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Peso (kg)</label>
                        <input type="number" step="0.01" name="peso" value="<?= $pet['peso'] ?: 0 ?>" required>
                    </div>
                    <button type="submit" class="btn-primary">Guardar Consulta</button>
                </form>
            </div>
            <div class="card">
                <h3>Historial Clínico</h3>
                <?php if (empty($visits)): ?>
                    <p>No hay registros clínicos para esta mascota.</p>
                <?php else: ?>
                    <?php foreach ($visits as $visit): ?>
                        <div class="clinical-record">
                            <div class="record-header">
                                <strong><?= $visit['fecha'] ?></strong> — <?= htmlspecialchars($visit['motivo_consulta']) ?>
                            </div>
                            <div class="record-details">
                                <p><strong>Diagnóstico:</strong> <?= nl2br(htmlspecialchars($visit['diagnostico'])) ?></p>
                                <p><strong>Tratamiento:</strong> <?= nl2br(htmlspecialchars($visit['tratamiento'])) ?></p>
                                <p><strong>Peso:</strong> <?= $visit['peso'] ?> kg</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>