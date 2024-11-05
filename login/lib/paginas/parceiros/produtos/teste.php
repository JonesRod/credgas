
<script>
// ------Função para mostrar o conteúdo da aba selecionada
function mostrarConteudo(aba, element) {
    // Oculta todos os conteúdos das abas
    var conteudos = document.querySelectorAll('.conteudo-aba');
    conteudos.forEach(function(conteudo) {
        conteudo.style.display = 'none';
    });

    // Remove a classe 'active' de todas as abas
    var tabs = document.querySelectorAll('.tab');
    tabs.forEach(function(tab) {
        tab.classList.remove('active');
    });

    // Mostra o conteúdo da aba clicada
    var conteudoAba = document.getElementById('conteudo-' + aba);
    conteudoAba.style.display = 'block';

    // Limpa o campo de pesquisa específico e atualiza os produtos
    if (aba === 'catalogo') {
        document.getElementById('pesquisaCatalogo').value = '';
        atualizarProdutos('catalogo'); // Chama a função para atualizar os produtos do catálogo
    } else if (aba === 'promocoes') {
        document.getElementById('pesquisaPromocoes').value = '';
        atualizarProdutos('promocoes'); // Chama a função para atualizar os produtos das promoções
    }

    // Adiciona a classe 'active' à aba clicada
    element.classList.add('active');
}

// Função para atualizar os produtos exibidos
function atualizarProdutos(tipo) {
    let pesquisaInput = document.getElementById('pesquisa' + tipo.charAt(0).toUpperCase() + tipo.slice(1));
    let query = pesquisaInput.value.trim().toLowerCase();

    let listaProdutos = document.querySelectorAll('#conteudo-' + tipo + ' .produto-item');
    let encontrouProduto = false;

    listaProdutos.forEach(function(produto) {
        let nomeProduto = produto.querySelector('.produto-nome').textContent.toLowerCase();
        
        if (query === '' || nomeProduto.includes(query)) {
            produto.style.display = 'block';
            encontrouProduto = true;
        } else {
            produto.style.display = 'none';
        }
    });

    // Exibe mensagem se nenhum produto encontrado
    let mensagemNaoEncontrado = document.getElementById(tipo + '-nao-encontrado');
    if (!encontrouProduto && query !== '') {
        mensagemNaoEncontrado.style.display = 'block';
    } else {
        mensagemNaoEncontrado.style.display = 'none';
    }
}

// Define que a aba "catalogo" está ativa ao carregar a página
window.onload = function() {
    mostrarConteudo('catalogo', document.querySelector('.tab.active'));
};

</script>
