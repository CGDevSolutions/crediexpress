<?php
session_start();
header("Content-Type: application/json"); // Indicar que la respuesta es JSON

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['password'];

    // Credenciales de Supabase
    $supabaseUrl = 'https://ipijnjtploinnenqisko.supabase.co'; // Tu URL de Supabase
    $supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImlwaWpuanRwbG9pbm5lbnFpc2tvIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDA3NzY2MjMsImV4cCI6MjA1NjM1MjYyM30.WTzi3HOb8BzuLOgZs4J2o0higyIP0969Mr-XH0IteSY'; // Tu clave API

    // Consulta a la tabla de usuarios en Supabase
    $url = $supabaseUrl . '/rest/v1/usuarios?nombre_usuario=eq.' . urlencode($usuario);
    $options = [
        'http' => [
            'header'  => [
                'apikey: ' . $supabaseKey,
                'Authorization: Bearer ' . $supabaseKey,
                'Content-Type: application/json'
            ],
            'method'  => 'GET'
        ]
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        echo json_encode(["success" => false, "message" => "Error al conectar con Supabase"]);
        exit;
    }

    $users = json_decode($result, true);

    if (count($users) > 0) {
        $user = $users[0];

        // Comparar contraseñas directamente (sin hash)
        if ($contrasena === $user['contrasena']) {
            $_SESSION['usuario'] = $user['nombre_usuario'];
            $_SESSION['rol'] = $user['rol'];

            echo json_encode(["success" => true, "usuario" => $user['nombre_usuario'], "rol" => $user["rol"]]);
        } else {
            echo json_encode(["success" => false, "message" => "Usuario o contraseña incorrectos"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Usuario o contraseña incorrectos"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
}
?>