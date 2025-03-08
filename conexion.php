<?php
$servername = "localhost";
$username = "root"; 
$password = "08041997"; 
$dbname = "bd_prestamos";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>