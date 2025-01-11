<?php
include('../../conexao.php');

// Obtém os filtros enviados
$cidade = $_POST['cidade'];
$uf = $_POST['uf'];
$categoria = $_POST['categoria'];
$status = json_decode($_POST['statusParc'], true);

// Monta a consulta SQL
$sql = "SELECT * FROM meus_parceiros WHERE 1=1";

// Filtro por cidade
if (!empty($cidade)) {
    $sql .= " AND cidade = '" . $mysqli->real_escape_string($cidade) . "'";
}

// Filtro por estado (UF)
if (!empty($uf)) {
    $sql .= " AND estado = '" . $mysqli->real_escape_string($uf) . "'";
}

// Filtro por categoria
if (!empty($categoria)) {
    $sql .= " AND categoria = '" . $mysqli->real_escape_string($categoria) . "'";
}

// Filtro por status
$statusConditions = [];
if (!empty($status)) {
    if (in_array("ativo", $status)) {
        $statusConditions[] = "status = 'ATIVO'";
    }
    if (in_array("inativo", $status)) {
        $statusConditions[] = "status = 'INATIVO'";
    }
}
if (!empty($statusConditions)) {
    $sql .= " AND (" . implode(' OR ', $statusConditions) . ")";
}

// Ordena por nome dos parceiros
$sql .= " ORDER BY nomeFantasia ASC";

// Executa a consulta
$result = $mysqli->query($sql);

// Verifica se a consulta foi executada com sucesso
if (!$result) {
    echo "Erro na consulta: " . $mysqli->error;
    exit;
}

// Conta o número de parceiros
$totalParceiros = $result->num_rows;

// Exibe a tabela apenas se houver parceiros
if ($totalParceiros > 0) {
    echo "<table>";
    /*echo "<thead><tr><th>Nome Fantasia</th><th>Cidade/Estado</th><th>Categoria</th><th>Status</th><th>Ações</th></tr></thead>";*/
    echo "<tbody>";

    // Exibe os parceiros
    while ($parceiro = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . date('d/m/Y', strtotime($parceiro['data_cadastro'])) . "</td>";
        echo "<td><img src='../parceiros/arquivos/" . $parceiro['logo'] . "' alt='Logo' class='imagem'></td>";
        echo "<td>" . htmlspecialchars($parceiro['nomeFantasia']) . "</td>";
        echo "<td>" . htmlspecialchars($parceiro['categoria']) . "</td>";
        echo "<td><a href='detalhes_parceiro.php?id=" . $parceiro['id'] . "' class='detalhes-link'>Ver Detalhes</a></td>";
        echo "</tr>";
    }

    echo '</tbody>';
    echo "</table>";
} else {
    echo "<tr><td colspan='100%'><div class='msg'>Nenhum parceiro encontrado.</div></td></tr>";
}

?>
<style>
.msg {

    text-align: center;
    font-weight: bold;
    width: 100%;
    padding: 15px 0;
    background-color: #f8f8f8;
    border: 0px solid #ddd;
    color: dimgray;
}

</style>