<?php

$host="localhost";
$usuario="root";
$password="";
$bd="sistema_judicial";
$puerto=3307;

$conn=new mysqli(
    $host,
    $usuario,
    $password,
    $bd,
    $puerto
);

if($conn->connect_error){

    die("Error de conexión: ".$conn->connect_error);

}

$conn->set_charset("utf8mb4");

?>