<?php
include("conexao.php");

$erro = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = strtolower(trim($_POST["email"]));
    $senha = $_POST["senha"];

    $stmt = $conn->prepare("SELECT id_usuario, nome_completo, senha_hash, status_conta FROM usuario WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();

    if ($usuario && password_verify($senha, $usuario["senha_hash"])) {
        if ($usuario["status_conta"] != "ativo") {
            $erro = "Sua conta está suspensa. Fale com a coordenação.";
        } else {
            $nomes = explode(" ", $usuario["nome_completo"]);

            $_SESSION["id_usuario"] = $usuario["id_usuario"];
            $_SESSION["nome"] = $usuario["nome_completo"];
            $_SESSION["primeiro_nome"] = $nomes[0];

            header("Location: index.php");
            exit;
        }
    } else {
        $erro = "E-mail ou senha incorretos.";
    }
}

include("includes/cabecalho.php");
?>

<div class="caixa-form">
    <h1>Entrar</h1>

    <?php if (isset($_GET["cadastro"])) { ?>
        <p class="aviso sucesso">Conta criada! Agora é só fazer o login.</p>
    <?php } ?>

    <?php if ($erro != "") { ?>
        <p class="aviso erro"><?php echo $erro; ?></p>
    <?php } ?>

    <form method="post" action="login.php">
        <label for="email">E-mail institucional</label>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>">

        <label for="senha">Senha</label>
        <input type="password" name="senha" id="senha">

        <button type="submit" class="botao">Entrar</button>
    </form>

    <p class="link-baixo">Ainda não tem conta? <a href="cadastro.php">Cadastre-se</a></p>
</div>

<?php include("includes/rodape.php"); ?>
