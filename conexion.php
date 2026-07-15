<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "veterinaria";
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
define('ALERT_THRESHOLD', 10);
define('CRITICAL_THRESHOLD', 2);
?>
