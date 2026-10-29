<?php
include("conexao.php");
include("includes/verifica_login.php");

$id = (int)$_GET["id"];
$id_usuario = $_SESSION["id_usuario"];

$conn->query("UPDATE anuncio SET status_anuncio = 'removido' WHERE id_anuncio = $id AND id_vendedor = $id_usuario");

if ($conn->affected_rows > 0) {
    $_SESSION["mensagem"] = "Anúncio excluído.";
}

header("Location: meus_anuncios.php");
exit;
?>
