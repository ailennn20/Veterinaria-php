<?php
include('conexion.php');

// Buscar mascota por nombre o ID
$pet = null;
$visits = [];
if (isset($_GET['pet_id'])) {
    $pet_id = (int)$_GET['pet_id'];
   $res = $conn->query("
    SELECT p.*, o.nombre as owner_name, o.telefono as owner_phone
    FROM mascotas p
    JOIN dueños o ON p.dueño_id = o.id
    WHERE p.id = $pet_id
");

    ");
    if ($res->num_rows > 0) {
        $pet = $res->fetch_assoc();
        $visits_res = $conn->query("
            SELECT * FROM historias_clinicas
            WHERE mascota_id = $pet_id
            ORDER BY fecha DESC
        ");
        while ($row = $visits_res->fetch_assoc()) {
            $visits[] = $row;
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_name'])) {
    $name = $conn->real_escape_string($_POST['search_name']);
    $res = $conn->query("
        SELECT p.*, o.nombre as owner_name, o.telefono as owner_phone
        FROM mascotas p
        JOIN dueños o ON p.dueño_id = o.id
        WHERE p.nombre LIKE '%$name%'
        LIMIT 1
    ");
    if ($res->num_rows > 0) {
        $pet = $res->fetch_assoc();
        $pet_id = $pet['id'];
        $visits_res = $conn->query("
            SELECT * FROM historias_clinicas
            WHERE mascota_id = $pet_id
            ORDER BY fecha DESC
        ");
        while ($row = $visits_res->fetch_assoc()) {
            $visits[] = $row;
        }
    }
}

// Agregar visita clínica
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_visit'])) {
    $pet_id = (int)$_POST['pet_id'];
    $fecha = $conn->real_escape_string($_POST['fecha']);
    $motivo = $conn->real_escape_string($_POST['motivo']);
    $diagnostico = $conn->real_escape_string($_POST['diagnostico']);
    $tratamiento = $conn->real_escape_string($_POST['tratamiento']);
    $peso = (float)$_POST['peso'];

    $conn->query("
        INSERT INTO historias_clinicas
        (mascota_id, fecha, motivo_consulta, diagnostico, tratamiento, peso)
        VALUES ($pet_id, '$fecha', '$motivo', '$diagnostico', '$tratamiento', $peso)
    ");
    $conn->query("UPDATE mascotas SET peso = $peso WHERE id = $pet_id");

    header("Location: historial_clinico.php?pet_id=$pet_id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Clínico</title>
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
            <h2><i class="icon-history"></i> Historial Clínico</h2>
            <form method="post" class="search-form">
                <input type="text" name="search_name" placeholder="Buscar mascota por nombre" required value="<?= isset($_POST['search_name']) ? htmlspecialchars($_POST['search_name']) : '' ?>">
                <button type="submit" class="btn-primary">Buscar</button>
            </form>
        </div>
        <?php if ($pet): ?>
            <div class="card">
                <h3><?= htmlspecialchars($pet['nombre']) ?></h3>
                <div class="pet-info">
                    <p><strong>Dueño:</strong> <?= htmlspecialchars($pet['owner_name']) ?></p>
                    <p><strong>Teléfono:</strong> <?= htmlspecialchars($pet['telefono']) ?></p>
                    <p><strong>Especie:</strong> <?= htmlspecialchars($pet['especie']) ?></p>
                    <p><strong>Peso actual:</strong> <?= htmlspecialchars($pet['peso']) ?> kg</p>
                </div>
            </div>
            <div class="card">
                <h3>Agregar Consulta</h3>
                <form method="post">
                    <input type="hidden" name="add_visit" value="1">
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
                        <textarea name="diagnostico" rows="2" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Tratamiento</label>
                        <textarea name="tratamiento" rows="2" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Peso (kg)</label>
                        <input type="number" step="0.01" name="peso" value="<?= $pet['peso'] ?>" required>
                    </div>
                    <button type="submit" class="btn-primary">Guardar</button>
                </form>
            </div>
            <div class="card">
                <h3>Historial de Consultas</h3>
                <?php if (empty($visits)): ?>
                    <p>No hay consultas registradas para esta mascota.</p>
                <?php else: ?>
                    <div class="clinical-history">
                        <?php foreach ($visits as $visit): ?>
                            <div class="clinical-record">
                                <div class="record-header">
                                    <span class="record-date"><?= $visit['fecha'] ?></span>
                                    <span class="record-motivo"><?= htmlspecialchars($visit['motivo_consulta']) ?></span>
                                </div>
                                <div class="record-details">
                                    <p><strong>Diagnóstico:</strong> <?= nl2br(htmlspecialchars($visit['diagnostico'])) ?></p>
                                    <p><strong>Tratamiento:</strong> <?= nl2br(htmlspecialchars($visit['tratamiento'])) ?></p>
                                    <p><strong>Peso:</strong> <?= $visit['peso'] ?> kg</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div class="card">
                <p class="alert">No se encontró ninguna mascota con ese nombre.</p>
            </div>
        <?php endif; ?>
    </main>
    <footer>
        <p>© 2025 Veterinaria Dr. Martín — Todos los derechos reservados</p>
    </footer>
</body>
</html>
