// Função para alternar o menu lateral
function toggleMenu() {
    var menuLateral = document.getElementById("menu-lateral");
    var notificacoesPopup = document.getElementById("painel-notificacoes");

    // Fecha as notificações se o menu for aberto
    if (notificacoesPopup.style.display === "block") {
        notificacoesPopup.style.display = "none";
    }

    // Alterna a visibilidade do menu lateral
    if (menuLateral.style.display === "block") {
        menuLateral.style.display = "none";
    } else {
        menuLateral.style.display = "block";
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

// Função para adicionar um método de pagamento (exemplo simples)
function adicionarMetodoPagamento() {
    alert('Adicionando novo método de pagamento...');
}

// Função para gerar dinamicamente a lista de solicitações de cadastro
function carregarSolicitacoes() {
    const solicitacoes = [
        { empresa: 'Empresa 1', cnpj: '12.345.678/0001-90', email: 'empresa1@example.com' },
        { empresa: 'Empresa 2', cnpj: '23.456.789/0001-90', email: 'empresa2@example.com' }
    ];

    const listaSolicitacoes = document.getElementById('lista-solicitacoes');
    listaSolicitacoes.innerHTML = ''; // Limpa a lista antes de carregar novos dados

    solicitacoes.forEach(solicitacao => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${solicitacao.empresa}</td>
            <td>${solicitacao.cnpj}</td>
            <td>${solicitacao.email}</td>
            <td>
                <button onclick="aprovarSolicitacao('${solicitacao.empresa}')">Aprovar</button>
                <button onclick="rejeitarSolicitacao('${solicitacao.empresa}')">Rejeitar</button>
            </td>
        `;
        listaSolicitacoes.appendChild(row);
    });
}

// Funções para aprovar ou rejeitar solicitações
function aprovarSolicitacao(empresa) {
    alert(`Solicitação da empresa ${empresa} aprovada!`);
}

function rejeitarSolicitacao(empresa) {
    alert(`Solicitação da empresa ${empresa} rejeitada.`);
}

// Função para verificar o tamanho da tela e ocultar ou mostrar a barra lateral
function checkScreenSize() {
    const menuLateral = document.getElementById('menu-lateral');
    const isMenuVisible = menuLateral.style.display === 'none' || menuLateral.style.display === ''; // Verifica se o menu está visível

    if (window.innerWidth < 768) { // Ajuste a largura conforme necessário
        menuLateral.style.display = 'none'; // Oculta a barra lateral em telas pequenas
    } else {
        menuLateral.style.display = isMenuVisible ? 'none' : 'block'; // Mantém o estado atual
    }
}

// Adiciona o evento de redimensionamento da janela
window.addEventListener('resize', checkScreenSize);

// Chama a função inicialmente para definir o estado correto da barra lateral
checkScreenSize();

// Carregar solicitações quando a página for carregada
window.onload = carregarSolicitacoes;

// Função para mostrar o conteúdo da aba selecionada
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
    document.getElementById('conteudo-' + aba).style.display = 'block';

    // Adiciona a classe 'active' à aba clicada
    element.classList.add('active');
}

// Define que a aba "Dashboard" está ativa ao carregar a página
window.onload = function() {
    mostrarConteudo('dashboard', document.querySelector('.tab.active'));
};
// Função para alternar as notificações
function toggleNotificacoes() {
    var notificacoesPopup = document.getElementById("painel-notificacoes");
    var menuLateral = document.getElementById("menu-lateral");

    // Fecha o menu lateral se as notificações forem abertas
    if (menuLateral.style.display === "block") {
        menuLateral.style.display = "none";
    }

    // Alterna a visibilidade das notificações
    if (notificacoesPopup.style.display === "block") {
        notificacoesPopup.style.display = "none";
    } else {
        notificacoesPopup.style.display = "block";
    }
}

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

// Atualize a contagem de notificações no início (exemplo: 3 notificações)
atualizarContagemNotificacoes(4);
