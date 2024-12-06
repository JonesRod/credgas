// Exibir ou ocultar o campo de frete com base na seleção
document.addEventListener("DOMContentLoaded", function() {
    const freteGratisSelect = document.getElementById('frete_gratis');
    const freteGroup = document.getElementById('frete-group');
    const valorFrete = document.getElementById('valor_frete');
    
    if (freteGratisSelect && freteGroup && valorFrete) {
        freteGratisSelect.addEventListener('change', function() {
            if (this.value === 'sim') {
                freteGroup.style.display = 'none'; // Oculta o campo de frete quando for frete grátis
                valorFrete.value = '0,00'; // Define o valor do frete como 0,00 se for frete grátis
            } else {
                freteGroup.style.display = 'block'; // Mostra o campo de frete quando não for frete grátis
            }
        });
    }
});

// Exibir ou ocultar o campo de frete com base na seleção
document.addEventListener("DOMContentLoaded", function() {
    const freteGratisSelect = document.getElementById('frete_gratis_promocao');
    const freteGroup = document.getElementById('frete-gratis-group');
    const valorFrete = document.getElementById('valor_frete_promocao');
    
    if (freteGratisSelect && freteGroup && valorFrete) {
        freteGratisSelect.addEventListener('change', function() {
            if (this.value === 'sim') {
                freteGroup.style.display = 'none'; // Oculta o campo de frete quando for frete grátis
                valorFrete.value = '0,00'; // Define o valor do frete como 0,00 se for frete grátis
            } else {
                freteGroup.style.display = 'block'; // Mostra o campo de frete quando não for frete grátis
            }
        });
    }
});

// Função para formatar o valor digitado no campo "valor_produto"
function formatarValor() {
    let valor_produto = document.getElementById('valor_produto').value.replace(/\D/g, '');  // Remove todos os caracteres não numéricos
    valor_produto = (valor_produto / 100).toFixed(2);           // Divide por 100 para ajustar para formato de decimal (0.00)

    valor_produto = valor_produto.replace('.', ',');            // Substitui o ponto pela vírgula
    valor_produto = valor_produto.replace(/\B(?=(\d{3})+(?!\d))/g, ".");  // Adiciona os pontos para separar os milhares

    document.getElementById('valor_produto').value = valor_produto;                        // Atualiza o valor no campo
    //console.log('oii');
    //chama a função para calcular
    calcularTaxa();
}

// Função para calcular o valor com taxa
function calcularTaxa() {
    let valorProduto = parseFloat(document.getElementById('valor_produto').value.replace(/\./g, '').replace(',', '.'));
    let taxa = document.getElementById('taxa').value;

    if (!isNaN(valorProduto)) {
        // Calcula o valor do produto com 10% de taxa
        let valorProdutoTaxa = valorProduto + ((valorProduto * taxa)/100);

        // Formata o número no formato "0,00" com separadores de milhares
        let valorFormatado = valorProdutoTaxa.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        // Exibe o valor formatado no campo de valor_promocao_taxa
        //valorFormatado = valorFormatado.replace('.', ',');            // Substitui o ponto pela vírgula
        valorFormatado = valorFormatado.replace(/\B(?=(\d{3})+(?!\d))/g, ".");  // Adiciona os pontos para separar os milhares

        document.getElementById('valor_produto_taxa').value = valorFormatado;
    } else {
        document.getElementById('valor_produto_taxa').value = "0,00";       
    }
    //console.log('oii');
    //formatarValorFrete();
}

// Função para formatar o valor digitado no campo "valor_promocao"
function formatarValorFrete() {
    let valor = document.getElementById('valor_frete').value.replace(/\D/g, '');  // Remove todos os caracteres não numéricos
    valor = (valor / 100).toFixed(2);           // Divide por 100 para ajustar para formato de decimal (0.00)

    valor = valor.replace('.', ',');            // Substitui o ponto pela vírgula
    valor = valor.replace(/\B(?=(\d{3})+(?!\d))/g, ".");  // Adiciona os pontos para separar os milhares

    document.getElementById('valor_frete').value = valor;                        // Atualiza o valor no campo
    //console.log('oii');
}
// Função para formatar o valor digitado no campo "valor_promocao"
function formatarValorPromocao() {
    let valor_promocao = document.getElementById('valor_promocao').value.replace(/\D/g, '');  // Remove todos os caracteres não numéricos
    
    valor_promocao = (valor_promocao / 100).toFixed(2);           // Divide por 100 para ajustar para formato de decimal (0.00)

    valor_promocao = valor_promocao.replace('.', ',');            // Substitui o ponto pela vírgula
    valor_promocao = valor_promocao.replace(/\B(?=(\d{3})+(?!\d))/g, ".");  // Adiciona os pontos para separar os milhares

    document.getElementById('valor_promocao').value = valor_promocao;                        // Atualiza o valor no campo

    //console.log('oii');
    //chama a função para calcular
    calcularTaxaPromocao();
}

// Função para calcular o valor com taxa
function calcularTaxaPromocao() {
    let valorPromocao = parseFloat(document.getElementById('valor_promocao').value.replace(/\./g, '').replace(',', '.'));
    let taxa = document.getElementById('taxa').value;

    if (!isNaN(valorPromocao)) {
        // Calcula o valor do produto com 10% de taxa
        let valorProdutoTaxa = valorPromocao + ((valorPromocao * taxa)/100);

        // Formata o número no formato "0,00" com separadores de milhares
        let valorFormatado = valorProdutoTaxa.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        // Exibe o valor formatado no campo de valor_promocao_taxa
        //valorFormatado = valorFormatado.replace('.', ',');            // Substitui o ponto pela vírgula
        valorFormatado = valorFormatado.replace(/\B(?=(\d{3})+(?!\d))/g, ".");  // Adiciona os pontos para separar os milhares

        document.getElementById('valor_promocao_taxa').value = valorFormatado;
    } else {
        document.getElementById('valor_promocao_taxa').value = "0,00";       
    }
    //console.log('oii');
    //formatarValorFrete();
}
// Função para formatar o valor digitado no campo "valor_promocao"
function formatarValorFretePromocao() {
    let valor = document.getElementById('valor_frete_promocao').value.replace(/\D/g, '');  // Remove todos os caracteres não numéricos
    valor = (valor / 100).toFixed(2);           // Divide por 100 para ajustar para formato de decimal (0.00)

    valor = valor.replace('.', ',');            // Substitui o ponto pela vírgula
    valor = valor.replace(/\B(?=(\d{3})+(?!\d))/g, ".");  // Adiciona os pontos para separar os milhares

    document.getElementById('valor_frete_promocao').value = valor;                        // Atualiza o valor no campo
    console.log('oii');
}


