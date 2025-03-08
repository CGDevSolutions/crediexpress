<?php
// Incluir el archivo de conexión a la base de datos
require_once 'conexion.php';

// Configurar el encabezado para devolver JSON
header('Content-Type: application/json');

// Obtener los datos del cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

// Extraer los datos
$id = $data['id'];
$nombre = $data['nombre'];
$dui = $data['dui'];
$fechaNacimiento = $data['fechaNacimiento'];
$edad = $data['edad'];
$telefono = $data['telefono'];
$genero = $data['genero'];
$direccion = $data['direccion'];

// Validar que los datos no estén vacíos
if (empty($id) || empty($nombre) || empty($dui) || empty($fechaNacimiento) || empty($edad) || empty($telefono) || empty($genero) || empty($direccion)) {
    http_response_code(400); // Solicitud incorrecta
    echo json_encode(["error" => "Faltan datos requeridos"]);
    exit;
}

// Actualizar el cliente en la base de datos
try {
    $sql = "UPDATE clientes SET 
            nombre_completo = ?, 
            dui = ?, 
            fecha_nacimiento = ?, 
            edad = ?, 
            telefono = ?, 
            genero = ?, 
            direccion = ?, 
            fecha_actualizacion = NOW() 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssisssi", $nombre, $dui, $fechaNacimiento, $edad, $telefono, $genero, $direccion, $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => "Cliente actualizado correctamente."]);
    } else {
        http_response_code(500); // Error del servidor
        echo json_encode(["error" => "Error al actualizar el cliente: " . $stmt->error]);
    }
} catch (Exception $e) {
    http_response_code(500); // Error del servidor
    echo json_encode(["error" => "Error en la base de datos: " . $e->getMessage()]);
} finally {
    // Cerrar la conexión a la base de datos
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>