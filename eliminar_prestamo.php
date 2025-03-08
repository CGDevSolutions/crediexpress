<?php
require_once 'conexion.php';

header('Content-Type: application/json');

$prestamoId = $_GET['id'] ?? null;

if (!$prestamoId) {
    http_response_code(400);
    echo json_encode(["error" => "ID del préstamo no proporcionado."]);
    exit;
}

try {
    // Eliminar el préstamo
    $sql = "DELETE FROM prestamos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $prestamoId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(["message" => "Préstamo eliminado correctamente."]);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Préstamo no encontrado."]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error en la base de datos: " . $e->getMessage()]);
}
?>