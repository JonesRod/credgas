/*function toggleMenu() {
    $('#imenu').toggleClass('aberto');
}
function abrirNaDiv(link) {
    var div = document.getElementById('iconteudo');
    div.innerHTML = '<object type="text/html" data="' + link + '" style="width:100%; height:100%;">';
}*/

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
    //console.log('oi');
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
    //console.log('oi');

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


// Função para fechar o menu lateral ao clicar fora dele
window.addEventListener('click', function(event) {
    const menu = document.getElementById('menu-lateral');
    const menuIcon = document.querySelector('.fas.fa-bars'); // Ícone de loja
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
    document.getElementById('conteudo-'+ aba).style.display = 'block';

    // Adiciona a classe 'active' à aba clicada
    element.classList.add('active');
    //console.log('eee');

}

// Define que a aba "catalogo" está ativa ao carregar a página
window.onload = function() {
    mostrarConteudo('catalogo', document.querySelector('.tab.active'));
};*/

// Função para abrir o conteúdo da notificação clicada
function abrirNotificacao(id) {
    alert('Abrindo conteúdo da notificação ' + id);
    // Aqui você pode adicionar a lógica para exibir o conteúdo da notificação
}

// Exemplo de atualização da contagem de notificações (chame essa função quando houver novas notificações)
function atualizarContagemNotificacoes(contagem) {
    const countElement = document.getElementById('notificacao-count');
    countElement.textContent = contagem;
    countElement.style.display = contagem > 0 ? 'block' : 'none';
}

