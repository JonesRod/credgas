<?php
// Conexão com o banco de dados
include("../../conexao.php");

$idParceiro = $_POST['id_parceiro'] ?? '';
$categoria = $_POST['categoria'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idParceiro = $_POST['id_parceiro'] ?? null;
    $categoria = $_POST['categoria_selecionada'] ?? null;

    // Debug: Verificar os dados recebidos
    var_dump($idParceiro, $categoria);
}
//die();
if (!empty($categoria)) {
    $catalogo = $mysqli->query("SELECT * FROM produtos 
    WHERE oculto != 'sim' 
    AND produto_aprovado = 'sim'
    AND categoria = '$categoria'")->fetch_all(MYSQLI_ASSOC);
    
    $promocao = $mysqli->query("SELECT * FROM produtos 
    WHERE oculto != 'sim' 
    AND produto_aprovado = 'sim'
    AND categoria = '$categoria' 
    AND promocao = 'sim'")->fetch_all(MYSQLI_ASSOC);
    
    $frete_gratis = $mysqli->query("SELECT * FROM produtos 
    WHERE (id_parceiro = '$idParceiro' 
    AND categoria = '$categoria' 
    AND oculto != 'sim' 
    AND produto_aprovado = 'sim'
    AND promocao = 'sim' 
    AND frete_gratis_promocao = 'sim') 
    OR (id_parceiro = '$idParceiro' 
    AND categoria = '$categoria' 
    AND oculto != 'sim' 
    AND produto_aprovado = 'sim'
    AND promocao = 'nao' 
    AND frete_gratis = 'sim')")->fetch_all(MYSQLI_ASSOC);
    
    $novidades = $mysqli->query("SELECT *, DATEDIFF(NOW(), data) AS dias_desde_cadastro 
    FROM produtos 
    WHERE id_parceiro = $idParceiro 
    AND categoria = '$categoria' 
    AND oculto != 'sim' 
    AND produto_aprovado = 'sim'")->fetch_all(MYSQLI_ASSOC);


    echo json_encode([
        "catalogo" => $catalogo,
        "promocao" => $promocao,
        "frete_gratis" => $frete_gratis,
        "novidades" => $novidades,
    ]);
} else {
    echo json_encode([
        "catalogo" => [],
        "promocao" => [],
        "frete_gratis" => [],
        "novidades" => [],
    ]);
}
?>
