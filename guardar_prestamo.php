<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data)) {
    http_response_code(400);
    echo json_encode(["error" => "Datos no proporcionados"]);
    exit;
}

$clienteId = $data['clienteId'];
$monto = $data['monto'];
$interes = $data['interes'];
$modalidad = $data['modalidad'];
$plazo = $data['plazo'];
$totalPagar = $data['totalPagar'];
$amortizacion = $data['amortizacion'];

if (empty($clienteId) || empty($monto) || empty($interes) || empty($modalidad) || empty($plazo) || empty($totalPagar) || empty($amortizacion)) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan datos requeridos"]);
    exit;
}

// Validar tipos de datos
if (!is_numeric($monto) || !is_numeric($interes) || !is_numeric($plazo) || !is_numeric($totalPagar)) {
    http_response_code(400);
    echo json_encode(["error" => "Datos numéricos inválidos"]);
    exit;
}

$servername = "localhost";
$username = "root";
$password = "08041997";
$dbname = "bd_prestamos";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión a la base de datos: " . $conn->connect_error]);
    exit;
}

$conn->begin_transaction();

try {
    // Verificar que el cliente exista
    $sqlCheckCliente = "SELECT id FROM clientes WHERE id = ?";
    $stmtCheckCliente = $conn->prepare($sqlCheckCliente);
    $stmtCheckCliente->bind_param("i", $clienteId);
    $stmtCheckCliente->execute();
    $stmtCheckCliente->store_result();

    if ($stmtCheckCliente->num_rows === 0) {
        throw new Exception("El cliente con ID $clienteId no existe.");
    }
    $stmtCheckCliente->close();

    // Guardar el préstamo
    $sql = "INSERT INTO prestamos (cliente_id, monto, interes, modalidad, plazo, total_pagar) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $conn->error);
    }
    $stmt->bind_param("idssid", $clienteId, $monto, $interes, $modalidad, $plazo, $totalPagar);

    if (!$stmt->execute()) {
        throw new Exception("Error al guardar el préstamo: " . $stmt->error);
    }

    $prestamoId = $stmt->insert_id;

    // Guardar la tabla de amortización
    foreach ($amortizacion as $cuota) {
        $fechaPago = date('Y-m-d', strtotime($cuota['fechaPago']));
        $fechaAbono = $cuota['fechaAbono'] ? date('Y-m-d', strtotime($cuota['fechaAbono'])) : null;

        $sqlAmortizacion = "INSERT INTO amortizacion (prestamo_id, cuota, valor_cuota, saldo_anterior, nuevo_saldo, fecha_pago, abono_realizado, fecha_abono) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtAmortizacion = $conn->prepare($sqlAmortizacion);
        if (!$stmtAmortizacion) {
            throw new Exception("Error al preparar la consulta de amortización: " . $conn->error);
        }
        $stmtAmortizacion->bind_param("iiddssds", $prestamoId, $cuota['cuota'], $cuota['valorCuota'], $cuota['saldoAnterior'], $cuota['nuevoSaldo'], $fechaPago, $cuota['abonoRealizado'], $fechaAbono);

        if (!$stmtAmortizacion->execute()) {
            throw new Exception("Error al guardar la cuota: " . $stmtAmortizacion->error);
        }
    }

    $conn->commit();
    echo json_encode([
        "success" => true,
        "message" => "Préstamo y tabla de amortización guardados correctamente.",
        "prestamoId" => $prestamoId
    ]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($stmtAmortizacion)) $stmtAmortizacion->close();
    $conn->close();
}
?>