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

// Função para fechar o menu lateral ao clicar fora dele
window.addEventListener('click', function(event) {
    const menu = document.getElementById('menu-lateral');
    const menuIcon = document.querySelector('.fas.fa-bars'); // Ícone de menu
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

// Define que a aba "Dashboard" está ativa ao carregar a página
window.onload = function() {
    mostrarConteudo('dashboard', document.querySelector('.tab.active'));
};
// Função para alternar entre abas principais
function mostrarConteudo(conteudoId, elemento) {
    // Esconde todos os conteúdos
    const conteudos = document.querySelectorAll('.conteudo-aba');
    conteudos.forEach(conteudo => conteudo.style.display = 'none');

    // Remove a classe 'active' de todas as abas principais
    const abas = document.querySelectorAll('.opcoes .tab');
    abas.forEach(aba => aba.classList.remove('active'));

    // Exibe o conteúdo correspondente e adiciona a classe 'active' à aba selecionada
    document.getElementById('conteudo-' + conteudoId).style.display = 'block';
    elemento.classList.add('active');

    // Se "Gerenciamento" for clicado, exibir "Parceiros" por padrão
    if (conteudoId === 'gerenciamento') {
        mostrarConteudoGerenciamento('parceiros', document.querySelector('.opcoes-gerenciamento .tab'));
    }
}

// Função para alternar entre sub-abas dentro de Gerenciamento
function mostrarConteudoGerenciamento(conteudoId, elemento) {
    // Esconde todos os conteúdos de gerenciamento
    const conteudos = document.querySelectorAll('#conteudo-gerenciamento .conteudo-aba');
    conteudos.forEach(conteudo => conteudo.style.display = 'none');

    // Remove a classe 'active' de todas as sub-abas
    const abas = document.querySelectorAll('.opcoes-gerenciamento .tab');
    abas.forEach(aba => aba.classList.remove('active'));

    // Exibe o conteúdo correspondente e adiciona a classe 'active' à sub-aba selecionada
    document.getElementById('conteudo-' + conteudoId).style.display = 'block';
    elemento.classList.add('active');
}
