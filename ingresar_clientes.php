<?php
header("Content-Type: application/json");

// Conexión a la base de datos
$servername = "localhost";
$username = "root";  // Cambia si usas otro usuario
$password = "08041997";  // Si tienes contraseña en MySQL, agrégala aquí
$database = "bd_prestamos";

$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Error de conexión: " . $conn->connect_error]));
}

// Verificar si la solicitud es POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die(json_encode(["success" => false, "message" => "Método no permitido"]));
}

// Recibir datos del formulario
$nombre = $_POST['nombre'] ?? null;
$dui = $_POST['dui'] ?? null;
$fechaNacimiento = $_POST['fechaNacimiento'] ?? null;
$edad = $_POST['edad'] ?? null;
$telefono = $_POST['telefono'] ?? null;
$genero = $_POST['genero'] ?? null;
$direccion = $_POST['direccion'] ?? null;

$referencias = [
    ["nombre_referencia" => $_POST['referencia1'] ?? null, "direccion_referencia" => $_POST['direccionRef1'] ?? null, "telefono_referencia" => $_POST['telefonoRef1'] ?? null],
   // ["nombre" => $_POST['referencia2'] ?? null, "direccion" => $_POST['direccionRef2'] ?? null, "telefono" => $_POST['telefonoRef2'] ?? null],
    //["nombre" => $_POST['referencia3'] ?? null, "direccion" => $_POST['direccionRef3'] ?? null, "telefono" => $_POST['telefonoRef3'] ?? null],
];

// Validar que no haya campos vacíos
if (!$nombre || !$dui || !$fechaNacimiento || !$edad || !$telefono || !$genero || !$direccion) {
    die(json_encode(["success" => false, "message" => "Todos los campos son obligatorios"]));
}

// Insertar cliente en la base de datos (las fechas se llenan automáticamente con TIMESTAMP)
$sql_cliente = "INSERT INTO clientes (nombre_completo, dui, fecha_nacimiento, edad, telefono, genero, direccion)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql_cliente);
$stmt->bind_param("sssisss", $nombre, $dui, $fechaNacimiento, $edad, $telefono, $genero, $direccion);

if ($stmt->execute()) {
    $cliente_id = $stmt->insert_id;

    // Insertar referencias personales
    $sql_referencia = "INSERT INTO referencias_personales (cliente_id, nombre_referencia, direccion_referencia, telefono_referencia) VALUES (?, ?, ?, ?)";
    $stmt_ref = $conn->prepare($sql_referencia);

    foreach ($referencias as $ref) {
        if ($ref["nombre_referencia"] && $ref["direccion_referencia"] && $ref["telefono_referencia"]) {
            $stmt_ref->bind_param("isss", $cliente_id, $ref["nombre_referencia"], $ref["direccion_referencia"], $ref["telefono_referencia"]);
            $stmt_ref->execute();
        }
    }

    echo json_encode(["success" => true, "message" => "Cliente guardado correctamente"]);
} else {
    echo json_encode(["success" => false, "message" => "Error al guardar el cliente"]);
}

// Cerrar conexiones
$stmt->close();
$conn->close();
?>
