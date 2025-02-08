// Exibir ou ocultar o campo de frete com base na seleção
document.getElementById('frete_gratis').addEventListener('change', function() {
    const freteGroup = document.getElementById('frete-group');
    if (this.value === 'sim') {
        freteGroup.style.display = 'none'; // Mostra o campo de frete
    } else {
        freteGroup.style.display = 'block'; // Oculta o campo de frete
        document.getElementById('valor_frete').value = '0,00'; // Limpa o campo se não for frete grátis
    }
});

// Função para formatar o valor digitado no campo "valor_produto"
function formatarValor(input) {
    let valor = input.value.replace(/\D/g, '');  // Remove todos os caracteres não numéricos
    valor = (valor / 100).toFixed(2);           // Divide por 100 para ajustar para formato de decimal (0.00)

    valor = valor.replace('.', ',');            // Substitui o ponto pela vírgula
    valor = valor.replace(/\B(?=(\d{3})+(?!\d))/g, ".");  // Adiciona os pontos para separar os milhares

    input.value = valor;                        // Atualiza o valor no campo

    //chama a função para calcular
    calcularTaxa();
}

// Função para calcular o valor com taxa
function calcularTaxa() {
    let valorProdutoInput = document.getElementById('valor_produto');
    let taxaInput = document.getElementById('taxa');
    let valorProdutoTaxaInput = document.getElementById('valor_produto_taxa');

    // Verifica se os inputs existem
    if (!valorProdutoInput || !taxaInput || !valorProdutoTaxaInput) {
        console.error("Elementos não encontrados.");
        return;
    }

    // Obtém e formata os valores
    let valorProduto = parseFloat(valorProdutoInput.value.replace(/\./g, '').replace(',', '.')) || 0;
    let taxa = parseFloat(taxaInput.value) || 0;

    // Calcula o valor com a taxa correta
    let valorProdutoTaxa = valorProduto + (valorProduto * taxa) / 100;

    // Formata o número no padrão brasileiro
    let valorFormatado = valorProdutoTaxa.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    // Exibe o valor formatado no campo
    valorProdutoTaxaInput.value = valorFormatado;
}


// Função para formatar o valor digitado no campo "valor_produto"
function formatarValorFrete(input) {
    let valor = input.value.replace(/\D/g, '');  // Remove todos os caracteres não numéricos
    valor = (valor / 100).toFixed(2);           // Divide por 100 para ajustar para formato de decimal (0.00)

    valor = valor.replace('.', ',');            // Substitui o ponto pela vírgula
    valor = valor.replace(/\B(?=(\d{3})+(?!\d))/g, ".");  // Adiciona os pontos para separar os milhares

    input.value = valor;                        // Atualiza o valor no campo

}


