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
    // Obtener detalles del préstamo y del cliente
    $sql = "SELECT p.*, c.nombre_completo, c.dui, c.edad
            FROM prestamos p
            INNER JOIN clientes c ON p.cliente_id = c.id
            WHERE p.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $prestamoId);
    $stmt->execute();
    $prestamo = $stmt->get_result()->fetch_assoc();

    if (!$prestamo) {
        http_response_code(404);
        echo json_encode(["error" => "Préstamo no encontrado."]);
        exit;
    }

    // Obtener la tabla de amortización
    $sql = "SELECT * FROM amortizacion WHERE prestamo_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $prestamoId);
    $stmt->execute();
    $amortizacion = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Obtener la cuota actual y el valor de la cuota
    $sql = "SELECT MAX(numero_cuota) AS cuota_actual, monto_cuota AS valor_cuota FROM amortizacion WHERE prestamo_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $prestamoId);
    $stmt->execute();
    $cuotaInfo = $stmt->get_result()->fetch_assoc();

    // Devolver los datos
    echo json_encode([
        "cliente" => [
            "nombre" => $prestamo['nombre_completo'],
            "dui" => $prestamo['dui'],
            "edad" => $prestamo['edad']
        ],
        "prestamo" => [
            "id" => $prestamo['id'],
            "monto" => $prestamo['monto'],
            "interes" => $prestamo['interes'],
            "modalidad" => $prestamo['modalidad'],
            "plazo" => $prestamo['plazo'],
            "total_pagar" => $prestamo['total_pagar'],
            "cuota_actual" => $cuotaInfo['cuota_actual'] || 0,
            "valor_cuota" => $cuotaInfo['valor_cuota'] || 0,
            "estado" => $prestamo['estado']
        ],
        "amortizacion" => $amortizacion
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error en la base de datos: " . $e->getMessage()]);
}
?>