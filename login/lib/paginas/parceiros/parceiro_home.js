
// Função para fechar o menu lateral ao clicar fora dele
window.addEventListener('click', function(event) {
    const menu = document.getElementById('menu-lateral');
    const menuIcon = document.querySelector('.fas.fa-store'); // Ícone de loja
    if (menu.style.display === 'block' && !menu.contains(event.target) && event.target !== menuIcon) {
        menu.style.display = 'none'; // Fecha a barra lateral
    }
});

window.addEventListener('click', function(event) {
    const notif = document.getElementById('painel-notificacoes');
    const notifIcon = document.querySelector('.fas.fa-bell');
    if (notif.style.display === 'block' && !notif.contains(event.target) && event.target !== notifIcon) {
        notif.style.display = 'none';
    }
});

function toggleMenu() {
    var menuLateral = document.getElementById("menu-lateral");
    var notificacoesPopup = document.getElementById("painel-notificacoes");

    if (notificacoesPopup.style.display === "block") {
        notificacoesPopup.style.display = "none";
    }
    if (menuLateral.style.display === "block") {
        menuLateral.style.display = "none";
    } else {
        menuLateral.style.display = "block";
    }
}

function toggleNotificacoes() {
    var notificacoesPopup = document.getElementById("painel-notificacoes");
    var menuLateral = document.getElementById("menu-lateral");

    if (menuLateral.style.display === "block") {
        menuLateral.style.display = "none";
    }
    if (notificacoesPopup.style.display === "block") {
        notificacoesPopup.style.display = "none";
    } else {
        notificacoesPopup.style.display = "block";
    }

}

// Função para esconder a barra lateral ao clicar em qualquer item
function hideMenuOnClick() {
    const menu = document.getElementById('menu-lateral');
    menu.style.display = 'none';
}
// Adiciona o evento de clique a cada item do menu lateral
document.querySelectorAll('#menu-lateral ul li, #menu-lateral a').forEach(item => {
    item.addEventListener('click', hideMenuOnClick);
});

// ------Função para mostrar o conteúdo da aba selecionada
/*function mostrarConteudo(aba, element) {

    // Oculta todos os conteúdos das abas
    //console.log('eee');
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
    document.getElementById('conteudo-' + aba).style.display = 'block';

    // Adiciona a classe 'active' à aba clicada
    element.classList.add('active');
    //console.log('eee');

}

// Define que a aba "catalogo" está ativa ao carregar a página
window.onload = function() {
    mostrarConteudo('catalogo', document.querySelector('.tab.active'));
};*/


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

    // Limpa o campo de pesquisa específico baseado no ID da aba selecionada
    if (aba === 'catalogo') {
        document.getElementById('inputPesquisaCatalogo').value = '';
    } else if (aba === 'promocoes') {
        document.getElementById('inputPesquisaPromocao').value = '';
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
