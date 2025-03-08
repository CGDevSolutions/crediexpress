<?php
// Incluir el archivo de conexión a la base de datos
require_once 'conexion.php';

// Configurar el encabezado para devolver JSON
header('Content-Type: application/json');

try {
    // Consulta SQL para obtener los clientes
    $sql = "SELECT id, nombre_completo, dui, fecha_nacimiento, edad, telefono, genero, direccion, fecha_actualizacion FROM clientes";

    // Ejecutar la consulta
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Error en la consulta SQL: " . $conn->error);
    }

    // Obtener los resultados y almacenarlos en un array
    $clientes = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $clientes[] = $row; // Agregar cada cliente al array
        }
    }

    // Devolver los clientes en formato JSON
    echo json_encode($clientes);
} catch (Exception $e) {
    // Manejar errores
    http_response_code(500); // Error del servidor
    echo json_encode(["error" => $e->getMessage()]);
} finally {
    // Cerrar la conexión a la base de datos
    if (isset($conn)) {
        $conn->close();
    }
}
?>