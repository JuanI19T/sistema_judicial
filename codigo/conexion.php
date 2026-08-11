<?php

$host = "localhost";
$usuario = "root";
$contrasena = "1234";
$base_datos = "sistema_judicial";
$puerto = 3307;

$conn = new mysqli(
    $host,
    $usuario,
    $contrasena,
    $base_datos,
    $puerto
);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>