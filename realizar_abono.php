<?php
require_once 'conexion.php';

header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$prestamoId = $data['prestamoId'] ?? null;
$montoAbono = $data['montoAbono'] ?? null;

if (!$prestamoId || !$montoAbono) {
    http_response_code(400);
    echo json_encode(["error" => "Datos incompletos."]);
    exit;
}

try {
    // Iniciar transacción
    $conn->begin_transaction();

    // Obtener el préstamo
    $sql = "SELECT * FROM prestamos WHERE id = ? FOR UPDATE";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $prestamoId);
    $stmt->execute();
    $prestamo = $stmt->get_result()->fetch_assoc();

    if (!$prestamo) {
        http_response_code(404);
        echo json_encode(["error" => "Préstamo no encontrado."]);
        exit;
    }

    // Validar que el monto no sea mayor al saldo pendiente
    if ($montoAbono > $prestamo['total_pagar']) {
        http_response_code(400);
        echo json_encode(["error" => "El monto del abono no puede ser mayor al saldo pendiente."]);
        exit;
    }

    // Calcular el nuevo saldo
    $nuevoSaldo = $prestamo['total_pagar'] - $montoAbono;

    // Insertar el abono en la tabla de amortización
    $sql = "INSERT INTO amortizacion (prestamo_id, cuota, valor_cuota, saldo_anterior, nuevo_saldo, fecha_pago, abono_realizado, fecha_abono)
            VALUES (?, ?, ?, ?, ?, NOW(), ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iidddd", $prestamoId, $prestamo['cuota_actual'], $prestamo['valor_cuota'], $prestamo['total_pagar'], $nuevoSaldo, $montoAbono);
    $stmt->execute();

    // Actualizar el préstamo
    $sql = "UPDATE prestamos SET total_pagar = ?, cuota_actual = cuota_actual + 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $nuevoSaldo, $prestamoId);
    $stmt->execute();

    // Confirmar la transacción
    $conn->commit();

    echo json_encode(["message" => "Abono realizado correctamente.", "nuevo_saldo" => $nuevoSaldo]);
} catch (Exception $e) {
    // Revertir la transacción en caso de error
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["error" => "Error en la base de datos: " . $e->getMessage()]);
}
?>