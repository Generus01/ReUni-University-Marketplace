<?php
include("conexao.php");

$id = 0;
if (isset($_GET["id"])) $id = (int)$_GET["id"];

$sql = "SELECT a.*, c.nome AS categoria, u.nome_completo, u.email, u.telefone, u.reputacao_media, cu.nome AS curso
        FROM anuncio a
        INNER JOIN categoria c ON c.id_categoria = a.id_categoria
        INNER JOIN usuario u ON u.id_usuario = a.id_vendedor
        LEFT JOIN curso cu ON cu.id_curso = u.id_curso
        WHERE a.id_anuncio = $id AND a.status_anuncio <> 'removido'";

$resultado = $conn->query($sql);
$anuncio = $resultado->fetch_assoc();

include("includes/cabecalho.php");

if (!$anuncio) {
    echo "<p class='vazio'>Anúncio não encontrado. <a href='index.php'>Voltar para a lista</a></p>";
    include("includes/rodape.php");
    exit;
}

$fotos = $conn->query("SELECT caminho FROM anuncio_foto WHERE id_anuncio = $id ORDER BY id_foto");

$nomes_condicao = array(
    "novo" => "Novo",
    "seminovo" => "Seminovo",
    "usado" => "Usado",
    "pecas" => "Para peças"
);

$dono = isset($_SESSION["id_usuario"]) && $_SESSION["id_usuario"] == $anuncio["id_vendedor"];
?>

<p class="voltar"><a href="index.php">&larr; Voltar para os anúncios</a></p>

<div class="detalhes">
    <div class="fotos">
        <?php if ($fotos->num_rows == 0) { ?>
            <div class="sem-foto grande">sem foto</div>
        <?php } ?>
        <?php while ($foto = $fotos->fetch_assoc()) { ?>
            <img src="<?php echo $foto['caminho']; ?>" alt="Foto do item">
        <?php } ?>
    </div>

    <div class="info">
        <?php if ($anuncio["status_anuncio"] != "disponivel") { ?>
            <span class="etiqueta"><?php echo ucfirst($anuncio["status_anuncio"]); ?></span>
        <?php } ?>

        <h1><?php echo htmlspecialchars($anuncio["titulo"]); ?></h1>

        <?php if ($anuncio["preco"] == 0) { ?>
            <p class="preco-grande doacao">Doação</p>
        <?php } else { ?>
            <p class="preco-grande">R$ <?php echo number_format($anuncio["preco"], 2, ",", "."); ?></p>
        <?php } ?>

        <table class="tabela-info">
            <tr><td>Categoria</td><td><?php echo $anuncio["categoria"]; ?></td></tr>
            <tr><td>Condição</td><td><?php echo $nomes_condicao[$anuncio["condicao"]]; ?></td></tr>
            <tr><td>Retirada</td><td><?php echo htmlspecialchars($anuncio["local_retirada"]); ?></td></tr>
            <tr><td>Publicado em</td><td><?php echo date("d/m/Y", strtotime($anuncio["data_publicacao"])); ?></td></tr>
        </table>

        <h3>Descrição</h3>
        <p class="descricao"><?php echo nl2br(htmlspecialchars($anuncio["descricao"])); ?></p>

        <div class="vendedor">
            <h3>Anunciante</h3>
            <p>
                <strong><?php echo htmlspecialchars($anuncio["nome_completo"]); ?></strong><br>
                <?php echo $anuncio["curso"]; ?><br>
                Reputação: <?php echo $anuncio["reputacao_media"]; ?> / 5
            </p>

            <?php if (isset($_SESSION["id_usuario"])) { ?>
                <p>
                    E-mail: <?php echo $anuncio["email"]; ?><br>
                    <?php if ($anuncio["telefone"] != "") { ?>
                        Telefone: <?php echo htmlspecialchars($anuncio["telefone"]); ?>
                    <?php } ?>
                </p>
            <?php } else { ?>
                <p class="texto-ajuda"><a href="login.php">Entre na sua conta</a> para ver o contato do anunciante.</p>
            <?php } ?>
        </div>

        <?php if ($dono) { ?>
            <div class="acoes">
                <a href="editar_anuncio.php?id=<?php echo $id; ?>" class="botao">Editar</a>
                <a href="excluir_anuncio.php?id=<?php echo $id; ?>" class="botao botao-perigo" onclick="return confirm('Tem certeza que quer excluir esse anúncio?')">Excluir</a>
            </div>
        <?php } ?>
    </div>
</div>

<?php include("includes/rodape.php"); ?>
