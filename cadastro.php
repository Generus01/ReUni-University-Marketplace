<?php
include("conexao.php");

$erro = "";
$nome = "";
$email = "";
$telefone = "";
$id_curso = "";
$id_campus = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"]);
    $email = strtolower(trim($_POST["email"]));
    $telefone = trim($_POST["telefone"]);
    $id_curso = $_POST["id_curso"];
    $id_campus = $_POST["id_campus"];
    $senha = $_POST["senha"];
    $confirmar = $_POST["confirmar_senha"];

    if ($nome == "" || $email == "" || $senha == "" || $id_curso == "" || $id_campus == "") {
        $erro = "Preencha todos os campos obrigatórios.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Digite um e-mail válido.";
    } else if (strlen($senha) < 6) {
        $erro = "A senha precisa ter pelo menos 6 caracteres.";
    } else if ($senha != $confirmar) {
        $erro = "As senhas não são iguais.";
    } else {
        $partes = explode("@", $email);
        $dominio = $partes[1];

        $stmt = $conn->prepare("SELECT id_instituicao FROM instituicao WHERE dominio_email = ?");
        $stmt->bind_param("s", $dominio);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows == 0) {
            $erro = "Use o seu e-mail institucional da faculdade (ex: nome@aluno.faculdadeexemplo.br).";
        } else {
            $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows > 0) {
                $erro = "Já existe uma conta com esse e-mail.";
            } else {
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("INSERT INTO usuario (nome_completo, email, senha_hash, id_curso, id_campus, telefone) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssiis", $nome, $email, $senha_hash, $id_curso, $id_campus, $telefone);

                if ($stmt->execute()) {
                    header("Location: login.php?cadastro=ok");
                    exit;
                } else {
                    $erro = "Não foi possível criar a conta. Tente de novo.";
                }
            }
        }
    }
}

$cursos = $conn->query("SELECT id_curso, nome FROM curso ORDER BY nome");
$campi = $conn->query("SELECT id_campus, nome FROM campus ORDER BY nome");

include("includes/cabecalho.php");
?>

<div class="caixa-form">
    <h1>Criar conta</h1>
    <p class="texto-ajuda">Só alunos da faculdade podem se cadastrar, por isso use seu e-mail institucional.</p>

    <?php if ($erro != "") { ?>
        <p class="aviso erro"><?php echo $erro; ?></p>
    <?php } ?>

    <form method="post" action="cadastro.php">
        <label for="nome">Nome completo *</label>
        <input type="text" name="nome" id="nome" maxlength="100" value="<?php echo htmlspecialchars($nome); ?>">

        <label for="email">E-mail institucional *</label>
        <input type="email" name="email" id="email" maxlength="100" value="<?php echo htmlspecialchars($email); ?>">

        <label for="telefone">Telefone / WhatsApp</label>
        <input type="text" name="telefone" id="telefone" maxlength="20" value="<?php echo htmlspecialchars($telefone); ?>">

        <div class="linha">
            <div>
                <label for="id_curso">Curso *</label>
                <select name="id_curso" id="id_curso">
                    <option value="">Selecione</option>
                    <?php while ($curso = $cursos->fetch_assoc()) { ?>
                        <option value="<?php echo $curso['id_curso']; ?>" <?php if ($id_curso == $curso['id_curso']) echo "selected"; ?>>
                            <?php echo $curso['nome']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div>
                <label for="id_campus">Campus *</label>
                <select name="id_campus" id="id_campus">
                    <option value="">Selecione</option>
                    <?php while ($campus = $campi->fetch_assoc()) { ?>
                        <option value="<?php echo $campus['id_campus']; ?>" <?php if ($id_campus == $campus['id_campus']) echo "selected"; ?>>
                            <?php echo $campus['nome']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <label for="senha">Senha *</label>
        <input type="password" name="senha" id="senha">

        <label for="confirmar_senha">Confirmar senha *</label>
        <input type="password" name="confirmar_senha" id="confirmar_senha">

        <button type="submit" class="botao">Cadastrar</button>
    </form>

    <p class="link-baixo">Já tem conta? <a href="login.php">Entrar</a></p>
</div>

<?php include("includes/rodape.php"); ?>
