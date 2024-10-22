// Exibir ou ocultar o campo de frete com base na seleção
/*document.getElementById('frete_gratis').addEventListener('change', function() {
    const freteGroup = document.getElementById('frete-group');
    if (this.value === 'sim') {
        freteGroup.style.display = 'none'; // Mostra o campo de frete
    } else {
        freteGroup.style.display = 'block'; // Oculta o campo de frete
        document.getElementById('valor_frete').value = '0,00'; // Limpa o campo se não for frete grátis
    }
});*/

// Função para formatar o valor digitado no campo "valor_produto"
function formatarValor() {
    let valor_produto = document.getElementById('valor_produto').value.replace(/\D/g, '');  // Remove todos os caracteres não numéricos
    valor_produto = (valor_produto / 100).toFixed(2);           // Divide por 100 para ajustar para formato de decimal (0.00)

    valor_produto = valor_produto.replace('.', ',');            // Substitui o ponto pela vírgula
    valor_produto = valor_produto.replace(/\B(?=(\d{3})+(?!\d))/g, ".");  // Adiciona os pontos para separar os milhares

    document.getElementById('valor_produto').value = valor_produto;                        // Atualiza o valor no campo

    //chama a função para calcular
    calcularTaxa();
}

// Função para calcular o valor com taxa
function calcularTaxa() {
    let valorProduto = parseFloat(document.getElementById('valor_produto').value.replace(/\./g, '').replace(',', '.'));

    if (!isNaN(valorProduto)) {
        // Calcula o valor do produto com 10% de taxa
        let valorProdutoTaxa = valorProduto + (valorProduto * 0.10);

        // Formata o número no formato "0,00" com separadores de milhares
        let valorFormatado = valorProdutoTaxa.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        // Exibe o valor formatado no campo de valor_produto_taxa
        document.getElementById('valor_produto_taxa').value = valorFormatado;
    } else {
        document.getElementById('valor_produto_taxa').value = "0,00";       
    }
    //formatarValorFrete();
}

// Função para formatar o valor digitado no campo "valor_produto"
function formatarValorFrete() {
    let valor = document.getElementById('valor_frete').value.replace(/\D/g, '');  // Remove todos os caracteres não numéricos
    valor = (valor / 100).toFixed(2);           // Divide por 100 para ajustar para formato de decimal (0.00)

    valor = valor.replace('.', ',');            // Substitui o ponto pela vírgula
    valor = valor.replace(/\B(?=(\d{3})+(?!\d))/g, ".");  // Adiciona os pontos para separar os milhares

    document.getElementById('valor_frete').value = valor;                        // Atualiza o valor no campo
    //console.log('oii');
}


