<?php
include("conexao.php");
include("includes/verifica_login.php");

$erro = "";
$titulo = "";
$descricao = "";
$id_categoria = "";
$condicao = "";
$preco = "";
$local_retirada = "";
$doacao = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST["titulo"]);
    $descricao = trim($_POST["descricao"]);
    $id_categoria = $_POST["id_categoria"];
    $condicao = $_POST["condicao"];
    $preco = trim($_POST["preco"]);
    $local_retirada = trim($_POST["local_retirada"]);
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
        $id_vendedor = $_SESSION["id_usuario"];

        $stmt = $conn->prepare("INSERT INTO anuncio (id_vendedor, id_categoria, titulo, descricao, preco, condicao, local_retirada) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissdss", $id_vendedor, $id_categoria, $titulo, $descricao, $valor, $condicao, $local_retirada);

        if ($stmt->execute()) {
            $id_anuncio = $conn->insert_id;
            $limite_fotos = 5;
            include("includes/salvar_fotos.php");

            $_SESSION["mensagem"] = "Anúncio publicado! " . $aviso_fotos;
            header("Location: anuncio.php?id=" . $id_anuncio);
            exit;
        } else {
            $erro = "Erro ao salvar o anúncio.";
        }
    }
}

$categorias = $conn->query("SELECT id_categoria, nome FROM categoria ORDER BY nome");
$texto_botao = "Publicar anúncio";

include("includes/cabecalho.php");
?>

<div class="caixa-form larga">
    <h1>Novo anúncio</h1>
    <?php include("includes/form_anuncio.php"); ?>
</div>

<?php include("includes/rodape.php"); ?>
