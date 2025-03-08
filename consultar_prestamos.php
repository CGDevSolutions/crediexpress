<?php
require_once 'conexion.php';

header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$nombreCliente = $data['nombreCliente'] ?? null;
$duiCliente = $data['duiCliente'] ?? null;

try {
    $sql = "SELECT p.id, p.monto, p.interes, p.modalidad, p.plazo, p.total_pagar, p.estado, c.nombre_completo, c.dui
            FROM prestamos p
            INNER JOIN clientes c ON p.cliente_id = c.id";

    if ($nombreCliente || $duiCliente) {
        $sql .= " WHERE ";
        $conditions = [];
        if ($nombreCliente) {
            $conditions[] = "c.nombre_completo LIKE ?";
        }
        if ($duiCliente) {
            $conditions[] = "c.dui LIKE ?";
        }
        $sql .= implode(" OR ", $conditions);

        $stmt = $conn->prepare($sql);
        if ($nombreCliente && $duiCliente) {
            $nombreClienteLike = "%$nombreCliente%";
            $duiClienteLike = "%$duiCliente%";
            $stmt->bind_param("ss", $nombreClienteLike, $duiClienteLike);
        } elseif ($nombreCliente) {
            $nombreClienteLike = "%$nombreCliente%";
            $stmt->bind_param("s", $nombreClienteLike);
        } elseif ($duiCliente) {
            $duiClienteLike = "%$duiCliente%";
            $stmt->bind_param("s", $duiClienteLike);
        }
    } else {
        $stmt = $conn->prepare($sql);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $prestamos = [];
    while ($row = $result->fetch_assoc()) {
        $prestamos[] = $row;
    }

    echo json_encode($prestamos);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error en la base de datos: " . $e->getMessage()]);
}
?>