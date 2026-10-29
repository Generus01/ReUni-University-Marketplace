<?php
include("conexao.php");
include("includes/verifica_login.php");

$id_usuario = $_SESSION["id_usuario"];

$sql = "SELECT a.id_anuncio, a.titulo, a.preco, a.status_anuncio, a.data_publicacao,
        (SELECT caminho FROM anuncio_foto f WHERE f.id_anuncio = a.id_anuncio ORDER BY f.id_foto LIMIT 1) AS foto
        FROM anuncio a
        WHERE a.id_vendedor = $id_usuario AND a.status_anuncio <> 'removido'
        ORDER BY a.data_publicacao DESC";

$anuncios = $conn->query($sql);

include("includes/cabecalho.php");
?>

<div class="titulo-pagina">
    <h1>Meus anúncios</h1>
    <a href="novo_anuncio.php" class="botao">+ Novo anúncio</a>
</div>

<?php if ($anuncios->num_rows == 0) { ?>
    <p class="vazio">Você ainda não publicou nenhum anúncio.</p>
<?php } else { ?>
    <table class="tabela">
        <tr>
            <th></th>
            <th>Item</th>
            <th>Preço</th>
            <th>Situação</th>
            <th>Publicado</th>
            <th></th>
        </tr>
        <?php while ($anuncio = $anuncios->fetch_assoc()) { ?>
            <tr>
                <td class="miniatura">
                    <?php if ($anuncio["foto"] != "") { ?>
                        <img src="<?php echo $anuncio["foto"]; ?>" alt="">
                    <?php } ?>
                </td>
                <td><a href="anuncio.php?id=<?php echo $anuncio["id_anuncio"]; ?>"><?php echo htmlspecialchars($anuncio["titulo"]); ?></a></td>
                <td>
                    <?php
                    if ($anuncio["preco"] == 0) {
                        echo "Doação";
                    } else {
                        echo "R$ " . number_format($anuncio["preco"], 2, ",", ".");
                    }
                    ?>
                </td>
                <td><?php echo ucfirst($anuncio["status_anuncio"]); ?></td>
                <td><?php echo date("d/m/Y", strtotime($anuncio["data_publicacao"])); ?></td>
                <td class="links">
                    <a href="editar_anuncio.php?id=<?php echo $anuncio["id_anuncio"]; ?>">Editar</a>
                    <a href="excluir_anuncio.php?id=<?php echo $anuncio["id_anuncio"]; ?>" class="perigo" onclick="return confirm('Tem certeza que quer excluir esse anúncio?')">Excluir</a>
                </td>
            </tr>
        <?php } ?>
    </table>
<?php } ?>

<?php include("includes/rodape.php"); ?>
