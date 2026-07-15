<?php
include('conexion.php');

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $usuario = $_POST['usuario'];
    $clave = password_hash($_POST['clave'], PASSWORD_BCRYPT);
    $rol = $_POST['rol'];
    $conn->query("INSERT INTO usuarios (usuario, clave, rol) VALUES ('$usuario','$clave','$rol')");
}
$usuarios = $conn->query("SELECT * FROM usuarios");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Usuarios</title>
<link rel="stylesheet" href="estilos.css">
</head>
<body>
<header><h2>Gestión de Usuarios</h2></header>
<div class="container">
<div class="card">
<h3>Agregar Usuario</h3>
<form method="post">
    <label>Usuario</label>
    <input type="text" name="usuario" required>
    <label>Contraseña</label>
    <input type="password" name="clave" required>
    <label>Rol</label>
    <select name="rol">
        <option value="admin">Administrador</option>
        <option value="veterinario">Veterinario</option>
        <option value="empleado">Empleado</option>
    </select>
    <input type="submit" value="Guardar Usuario">
</form>
</div>

<div class="card">
<h3>Usuarios Registrados</h3>
<table>
<tr><th>Usuario</th><th>Rol</th></tr>
<?php while($u=$usuarios->fetch_assoc()){ ?>
<tr>
<td><?= $u['usuario'] ?></td>
<td><?= $u['rol'] ?></td>
</tr>
<?php } ?>
</table>
</div>
</div>
</body>
</html>
