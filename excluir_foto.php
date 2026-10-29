<?php
include("conexao.php");
include("includes/verifica_login.php");

$id_foto = (int)$_GET["id"];
$id_usuario = $_SESSION["id_usuario"];

$sql = "SELECT f.id_foto, f.caminho, f.id_anuncio
        FROM anuncio_foto f
        INNER JOIN anuncio a ON a.id_anuncio = f.id_anuncio
        WHERE f.id_foto = $id_foto AND a.id_vendedor = $id_usuario";

$resultado = $conn->query($sql);
$foto = $resultado->fetch_assoc();

if (!$foto) {
    header("Location: meus_anuncios.php");
    exit;
}

if (file_exists($foto["caminho"])) {
    unlink($foto["caminho"]);
}

$conn->query("DELETE FROM anuncio_foto WHERE id_foto = $id_foto");

header("Location: editar_anuncio.php?id=" . $foto["id_anuncio"]);
exit;
?>
