// Exibir ou ocultar o campo de frete com base na seleção
document.getElementById('frete_gratis').addEventListener('change', function() {
    const freteGroup = document.getElementById('frete-group');
    if (this.value === 'sim') {
        freteGroup.style.display = 'none'; // Mostra o campo de frete
    } else {
        freteGroup.style.display = 'block'; // Oculta o campo de frete
        document.getElementById('valor_frete').value = ''; // Limpa o campo se não for frete grátis
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
}

// Função para formatar o valor digitado no campo "valor_produto"
function formatarValorFrete(input) {
    let valor = input.value.replace(/\D/g, '');  // Remove todos os caracteres não numéricos
    valor = (valor / 100).toFixed(2);           // Divide por 100 para ajustar para formato de decimal (0.00)

    valor = valor.replace('.', ',');            // Substitui o ponto pela vírgula
    valor = valor.replace(/\B(?=(\d{3})+(?!\d))/g, ".");  // Adiciona os pontos para separar os milhares

    input.value = valor;                        // Atualiza o valor no campo

}

let imageCount = 0; // Contador de imagens adicionadas

function addImage(input) {
    const files = input.files;
    const container = document.getElementById('imagePreviewContainer');

    for (let i = 0; i < files.length; i++) {
        if (imageCount < 6) { // Limitar a 6 imagens
            const reader = new FileReader();
            reader.onload = function(e) {
                const imagePreview = document.createElement('div');
                imagePreview.className = 'image-upload';
                imagePreview.innerHTML = `
                    <img src="${e.target.result}" alt="Imagem do Produto ${imageCount + 1}" />
                    <i class="fas fa-trash delete-icon" onclick="removeImage(this)"></i>
                `;
                container.appendChild(imagePreview);
                imageCount++;

                // Se já houver 6 imagens, esconder o ícone de adicionar
                if (imageCount >= 6) {
                    document.getElementById('produtoImagens').style.display = 'none';
                }
            }
            reader.readAsDataURL(files[i]);
        }
    }
}

function removeImage(element) {
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    imagePreviewContainer.removeChild(element.parentElement); // Remove a imagem e o ícone de lixeira
    imageCount--; // Decrementar o contador

    // Se houver espaço para adicionar imagens, mostrar o ícone de adicionar novamente
    if (imageCount < 6) {
        document.getElementById('produtoImagens').style.display = 'block';
    }
}




