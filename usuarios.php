<?php
// Incluir el archivo de conexión
require_once 'conexion.php';

// Datos del nuevo usuario recibidos del formulario
$nombre_usuario = $_POST['nombreUsuario'];
$contraseña = $_POST['passwordUsuario'];
$rol = $_POST['rolUsuario'];

try {
    // Preparar la consulta SQL
    $query = "INSERT INTO usuarios (nombre_usuario, contrasena, rol) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);

    // Vincular los parámetros
    $stmt->bind_param("sss", $nombre_usuario, $contraseña, $rol);

    // Ejecutar la consulta
    $stmt->execute();

    echo "Nuevo usuario insertado correctamente.";
} catch (Exception $e) {
    echo "Error al insertar el usuario: " . $e->getMessage();
}
?>
