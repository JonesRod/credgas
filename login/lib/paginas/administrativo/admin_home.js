// Função para alternar a exibição do menu lateral em telas pequenas
function toggleMenu() {
    var menu = document.getElementById('menu-lateral');
    if (menu.style.display === 'block') {
        menu.style.display = 'none';
    } else {
        menu.style.display = 'block';
    }
}

// Função para adicionar um novo método de pagamento (exemplo simples)
function adicionarMetodoPagamento() {
    alert('Função para adicionar um método de pagamento');
}
