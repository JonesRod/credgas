// Obtém o ID da sessão do PHP
var sessionId = <?php echo json_encode($id); ?>;

function abrirNotificacao(id) {
    let url = ""; // Inicializa a URL como uma string vazia

    // Define a URL com base no ID da notificação
    switch (id) {
        case 1:
            url = `detalhes_parceiro.php?session_id=${sessionId}`;
            break;
        case 2:
            url = `detalhes_crediario.php?session_id=${sessionId}`;
            break;
        case 3:
            url = `detalhes_novos_produtos.php?session_id=${sessionId}`;
            break;
        case 4:
            url = `detalhes_edicao_produtos.php?session_id=${sessionId}`;
            break;
        case 5:
            url = `detalhes_mensagens.php?session_id=${sessionId}`;
            break;
        default:
            console.error("ID de notificação inválido:", id);
            return; // Sai da função se o ID não for válido
    }

    // Redireciona para a URL correspondente
    window.location.href = url;
}



// Obtém o ID da sessão do PHP
        var sessionId = <?php echo $id; ?>;

        function abrirNotificacao(id) {
            
            // Redireciona para a página de detalhes com o ID da notificação e o ID da sessão
            var url = `detalhes_notificacao.php?id=${id}&session_id=${sessionId}`;
            //console.log("Redirecionando para:", url);
            
            // Verifica se a URL está correta antes de redirecionar
            window.location.href = url;
        }