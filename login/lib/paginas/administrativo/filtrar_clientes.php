<?php
include('../../conexao.php');

// Obtém os filtros enviados
$cidade = $_POST['cidade'];
$uf = $_POST['uf'];
$status = json_decode($_POST['statusCli'], true);

// Monta a consulta SQL
$sql = "SELECT * FROM meus_clientes WHERE 1=1";

// Filtro por cidade
if (!empty($cidade)) {
    $sql .= " AND cidade = '" . $mysqli->real_escape_string($cidade) . "'";
}

// Filtro por estado (UF)
if (!empty($uf)) {
    $sql .= " AND uf = '" . $mysqli->real_escape_string($uf) . "'";
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
    if (in_array("crediario", $status)) {
        $statusConditions[] = "status_crediario = 'Aprovado'";
    }
}
if (!empty($statusConditions)) {
    $sql .= " AND (" . implode(' OR ', $statusConditions) . ")";
}

// Ordena por nome dos parceiros
$sql .= " ORDER BY nome_completo ASC";

// Executa a consulta
$result = $mysqli->query($sql);

// Verifica se a consulta foi executada com sucesso
if (!$result) {
    echo "Erro na consulta: " . $mysqli->error;
    exit;
}

// Conta o número de clientes
$totalClientes = $result->num_rows;

// Exibe a tabela apenas se houver parceiros
if ($totalClientes > 0) {
    echo "<table>";
    /*echo "<thead><tr><th>Nome Fantasia</th><th>Cidade/Estado</th><th>Categoria</th><th>Status</th><th>Ações</th></tr></thead>";*/
    echo "<tbody>";
    while ($cliente = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . date('d/m/Y', strtotime($cliente['data_cadastro'])) . "</td>";
        echo "<td><img src='../clientes/arquivos/" . $cliente['imagem'] . "' alt='sem imagem' class='imagem'></td>";
        echo "<td>" . htmlspecialchars($cliente['nome_completo']) . "</td>";
        echo "<td><a href='detalhes_cliente.php?id=" . $cliente['id'] . "' class='detalhes-link'>Ver Detalhes</a></td>";
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
    padding: 15px 0;
    background-color: #f8f8f8;
    color: dimgray;
}
</style>
