<?php
include("conexao.php");
include("includes/verifica_login.php");

$id = (int)$_GET["id"];
$id_usuario = $_SESSION["id_usuario"];

$resultado = $conn->query("SELECT * FROM anuncio WHERE id_anuncio = $id AND id_vendedor = $id_usuario AND status_anuncio <> 'removido'");
$anuncio = $resultado->fetch_assoc();

if (!$anuncio) {
    header("Location: meus_anuncios.php");
    exit;
}

$erro = "";
$titulo = $anuncio["titulo"];
$descricao = $anuncio["descricao"];
$id_categoria = $anuncio["id_categoria"];
$condicao = $anuncio["condicao"];
$preco = number_format($anuncio["preco"], 2, ",", "");
$local_retirada = $anuncio["local_retirada"];
$status_anuncio = $anuncio["status_anuncio"];
$doacao = $anuncio["preco"] == 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST["titulo"]);
    $descricao = trim($_POST["descricao"]);
    $id_categoria = $_POST["id_categoria"];
    $condicao = $_POST["condicao"];
    $preco = trim($_POST["preco"]);
    $local_retirada = trim($_POST["local_retirada"]);
    $status_anuncio = $_POST["status_anuncio"];
    $doacao = isset($_POST["doacao"]);

    $valor = (float)str_replace(",", ".", $preco);
    if ($doacao) {
        $valor = 0;
    }

    if ($titulo == "" || $descricao == "" || $id_categoria == "" || $condicao == "") {
        $erro = "Preencha todos os campos obrigatórios.";
    } else if (!$doacao && $valor <= 0) {
        $erro = "Coloque um preço ou marque a opção de doação.";
    } else {
        $stmt = $conn->prepare("UPDATE anuncio SET id_categoria = ?, titulo = ?, descricao = ?, preco = ?, condicao = ?, local_retirada = ?, status_anuncio = ? WHERE id_anuncio = ?");
        $stmt->bind_param("issdsssi", $id_categoria, $titulo, $descricao, $valor, $condicao, $local_retirada, $status_anuncio, $id);
        $stmt->execute();

        $contagem = $conn->query("SELECT COUNT(*) AS total FROM anuncio_foto WHERE id_anuncio = $id");
        $linha = $contagem->fetch_assoc();

        $id_anuncio = $id;
        $limite_fotos = 5 - $linha["total"];
        include("includes/salvar_fotos.php");

        $_SESSION["mensagem"] = "Anúncio atualizado. " . $aviso_fotos;
        header("Location: anuncio.php?id=" . $id);
        exit;
    }
}

$categorias = $conn->query("SELECT id_categoria, nome FROM categoria ORDER BY nome");
$fotos = $conn->query("SELECT id_foto, caminho FROM anuncio_foto WHERE id_anuncio = $id ORDER BY id_foto");
$texto_botao = "Salvar alterações";

include("includes/cabecalho.php");
?>

<div class="caixa-form larga">
    <h1>Editar anúncio</h1>

    <?php if ($fotos->num_rows > 0) { ?>
        <p><strong>Fotos atuais</strong></p>
        <div class="fotos-atuais">
            <?php while ($foto = $fotos->fetch_assoc()) { ?>
                <div>
                    <img src="<?php echo $foto['caminho']; ?>" alt="Foto">
                    <a href="excluir_foto.php?id=<?php echo $foto['id_foto']; ?>" onclick="return confirm('Remover essa foto?')">remover</a>
                </div>
            <?php } ?>
        </div>
    <?php } ?>

    <?php include("includes/form_anuncio.php"); ?>
</div>

<?php include("includes/rodape.php"); ?>
