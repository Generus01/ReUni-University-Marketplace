<?php
include("conexao.php");

$busca = "";
$categoria = "";
$condicao = "";
$preco_min = "";
$preco_max = "";
$ordem = "recentes";

if (isset($_GET["busca"])) $busca = trim($_GET["busca"]);
if (isset($_GET["categoria"])) $categoria = $_GET["categoria"];
if (isset($_GET["condicao"])) $condicao = $_GET["condicao"];
if (isset($_GET["preco_min"])) $preco_min = $_GET["preco_min"];
if (isset($_GET["preco_max"])) $preco_max = $_GET["preco_max"];
if (isset($_GET["ordem"])) $ordem = $_GET["ordem"];

$sql = "SELECT a.id_anuncio, a.titulo, a.preco, a.condicao, a.local_retirada, c.nome AS categoria,
        (SELECT caminho FROM anuncio_foto f WHERE f.id_anuncio = a.id_anuncio ORDER BY f.id_foto LIMIT 1) AS foto
        FROM anuncio a
        INNER JOIN categoria c ON c.id_categoria = a.id_categoria
        WHERE a.status_anuncio = 'disponivel'";

if ($busca != "") {
    $termo = $conn->real_escape_string($busca);
    $sql .= " AND (a.titulo LIKE '%$termo%' OR a.descricao LIKE '%$termo%')";
}

if ($categoria != "") {
    $sql .= " AND a.id_categoria = " . (int)$categoria;
}

if ($condicao != "") {
    $sql .= " AND a.condicao = '" . $conn->real_escape_string($condicao) . "'";
}

if ($preco_min != "") {
    $sql .= " AND a.preco >= " . (float)str_replace(",", ".", $preco_min);
}

if ($preco_max != "") {
    $sql .= " AND a.preco <= " . (float)str_replace(",", ".", $preco_max);
}

if ($ordem == "menor_preco") {
    $sql .= " ORDER BY a.preco ASC";
} else if ($ordem == "maior_preco") {
    $sql .= " ORDER BY a.preco DESC";
} else {
    $sql .= " ORDER BY a.data_publicacao DESC";
}

$anuncios = $conn->query($sql);
$categorias = $conn->query("SELECT id_categoria, nome FROM categoria ORDER BY nome");

$nomes_condicao = array(
    "novo" => "Novo",
    "seminovo" => "Seminovo",
    "usado" => "Usado",
    "pecas" => "Para peças"
);

include("includes/cabecalho.php");
?>

<form method="get" action="index.php" class="barra-busca">
    <input type="text" name="busca" placeholder="O que você está procurando? Ex: livro de cálculo" value="<?php echo htmlspecialchars($busca); ?>">
    <button type="submit" class="botao">Buscar</button>
</form>

<div class="pagina-anuncios">

    <aside class="filtros">
        <form method="get" action="index.php">
            <input type="hidden" name="busca" value="<?php echo htmlspecialchars($busca); ?>">

            <h3>Filtrar</h3>

            <label for="categoria">Categoria</label>
            <select name="categoria" id="categoria">
                <option value="">Todas</option>
                <?php while ($cat = $categorias->fetch_assoc()) { ?>
                    <option value="<?php echo $cat['id_categoria']; ?>" <?php if ($categoria == $cat['id_categoria']) echo "selected"; ?>>
                        <?php echo $cat['nome']; ?>
                    </option>
                <?php } ?>
            </select>

            <label for="condicao">Condição</label>
            <select name="condicao" id="condicao">
                <option value="">Qualquer</option>
                <?php foreach ($nomes_condicao as $valor => $texto) { ?>
                    <option value="<?php echo $valor; ?>" <?php if ($condicao == $valor) echo "selected"; ?>><?php echo $texto; ?></option>
                <?php } ?>
            </select>

            <label>Preço (R$)</label>
            <div class="linha-preco">
                <input type="text" name="preco_min" placeholder="mín." value="<?php echo htmlspecialchars($preco_min); ?>">
                <input type="text" name="preco_max" placeholder="máx." value="<?php echo htmlspecialchars($preco_max); ?>">
            </div>

            <label for="ordem">Ordenar por</label>
            <select name="ordem" id="ordem">
                <option value="recentes" <?php if ($ordem == "recentes") echo "selected"; ?>>Mais recentes</option>
                <option value="menor_preco" <?php if ($ordem == "menor_preco") echo "selected"; ?>>Menor preço</option>
                <option value="maior_preco" <?php if ($ordem == "maior_preco") echo "selected"; ?>>Maior preço</option>
            </select>

            <button type="submit" class="botao">Aplicar</button>
            <a href="index.php" class="limpar">Limpar filtros</a>
        </form>
    </aside>

    <section class="lista">
        <p class="total">
            <?php echo $anuncios->num_rows; ?> anúncio(s) encontrado(s)
            <?php if ($busca != "") echo " para \"" . htmlspecialchars($busca) . "\""; ?>
        </p>

        <?php if ($anuncios->num_rows == 0) { ?>
            <p class="vazio">Nenhum item encontrado. Tente mudar os filtros.</p>
        <?php } ?>

        <div class="grade">
            <?php while ($anuncio = $anuncios->fetch_assoc()) { ?>
                <a href="anuncio.php?id=<?php echo $anuncio['id_anuncio']; ?>" class="item">
                    <?php if ($anuncio['foto'] != "") { ?>
                        <img src="<?php echo $anuncio['foto']; ?>" alt="<?php echo htmlspecialchars($anuncio['titulo']); ?>">
                    <?php } else { ?>
                        <div class="sem-foto">sem foto</div>
                    <?php } ?>

                    <div class="item-info">
                        <?php if ($anuncio['preco'] == 0) { ?>
                            <span class="preco doacao">Doação</span>
                        <?php } else { ?>
                            <span class="preco">R$ <?php echo number_format($anuncio['preco'], 2, ",", "."); ?></span>
                        <?php } ?>
                        <span class="titulo"><?php echo htmlspecialchars($anuncio['titulo']); ?></span>
                        <span class="detalhe"><?php echo $anuncio['categoria']; ?> · <?php echo $nomes_condicao[$anuncio['condicao']]; ?></span>
                        <?php if ($anuncio['local_retirada'] != "") { ?>
                            <span class="detalhe"><?php echo htmlspecialchars($anuncio['local_retirada']); ?></span>
                        <?php } ?>
                    </div>
                </a>
            <?php } ?>
        </div>
    </section>

</div>

<?php include("includes/rodape.php"); ?>
