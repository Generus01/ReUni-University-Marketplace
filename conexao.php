<?php
$servidor = "127.0.0.1";
$usuario = "root";
$senha = "";
$banco = "reuni";

$conn = new mysqli($servidor, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Erro ao conectar no banco: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
