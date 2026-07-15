<?php
include('conexion.php');

// Agregar dueño y mascota
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_owner_pet'])) {
    $owner_name = $conn->real_escape_string($_POST['owner_name']);
    $owner_phone = $conn->real_escape_string($_POST['owner_phone']);
    $pet_name = $conn->real_escape_string($_POST['pet_name']);
    $species = $conn->real_escape_string($_POST['species']);
    $breed = $conn->real_escape_string($_POST['breed']);
    $allergies = $conn->real_escape_string($_POST['allergies']);

    // Buscar si el dueño ya existe
    $owner_res = $conn->query("SELECT id FROM dueños WHERE nombre = '$owner_name' AND telefono = '$owner_phone'");
    if ($owner_res->num_rows > 0) {
        $owner = $owner_res->fetch_assoc();
        $owner_id = $owner['id'];
    } else {
        $conn->query("INSERT INTO dueños (nombre, telefono) VALUES ('$owner_name', '$owner_phone')");
        $owner_id = $conn->insert_id;
    }

    $conn->query("
        INSERT INTO mascotas
        (dueño_id, nombre, especie, raza, peso, alergias)
        VALUES ($owner_id, '$pet_name', '$species', '$breed', 0.0, '$allergies')
    ");
    header('Location: mascotas.php');
    exit;
}

// Listar dueños y mascotas
$owners = $conn->query("
    SELECT o.id as owner_id, o.nombre as owner_name, o.telefono,
           GROUP_CONCAT(p.id) as pet_ids,
           GROUP_CONCAT(p.nombre) as pet_names
    FROM dueños o
    LEFT JOIN mascotas p ON p.dueño_id = o.id
    GROUP BY o.id
    ORDER BY o.nombre
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mascotas</title>
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
            <h2><i class="icon-pet"></i> Mascotas</h2>
            <form method="post" class="owner-pet-form">
                <input type="hidden" name="add_owner_pet" value="1">
                <div class="form-group">
                    <label>Nombre del Dueño</label>
                    <input type="text" name="owner_name" required>
                </div>
                <div class="form-group">
                    <label>Teléfono del Dueño</label>
                    <input type="text" name="owner_phone" required>
                </div>
                <div class="form-group">
                    <label>Nombre de la Mascota</label>
                    <input type="text" name="pet_name" required>
                </div>
                <div class="form-group">
                    <label>Especie</label>
                    <input type="text" name="species" required>
                </div>
                <div class="form-group">
                    <label>Raza</label>
                    <input type="text" name="breed">
                </div>
                <div class="form-group">
                    <label>Alergias</label>
                    <input type="text" name="allergies">
                </div>
                <button type="submit" class="btn-primary">Agregar Mascota</button>
            </form>
        </div>
        <div class="card">
            <h3>Listado de Dueños y Mascotas</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Dueño</th>
                            <th>Teléfono</th>
                            <th>Mascotas</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $owners->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['owner_name']) ?></td>
                                <td><?= htmlspecialchars($row['telefono']) ?></td>
                                <td>
                                    <?php if ($row['pet_names']): ?>
                                        <?= htmlspecialchars(str_replace(',', ', ', $row['pet_names'])) ?>
                                    <?php else: ?>
                                        Sin mascotas
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['pet_ids']): ?>
                                        <?php
                                        $pet_ids = explode(',', $row['pet_ids']);
                                        foreach ($pet_ids as $pet_id) {
                                            echo "<a href='historial_clinico.php?pet_id=$pet_id' class='btn-info'>Historial</a> ";
                                        }
                                        ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <footer>
        <p>© 2025 Veterinaria Dr. Martín — Todos los derechos reservados</p>
    </footer>
</body>
</html>
