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

document.getElementById('produtoImagens').addEventListener('change', function(event) {
    const previewDiv = document.getElementById('preview');
    previewDiv.innerHTML = ''; // Limpa a pré-visualização anterior
    const files = event.target.files; // Arquivos selecionados

    // Limite máximo de 6 imagens
    const maxImagens = 6;

    if (files.length > maxImagens) {
        alert('Você pode selecionar no máximo ' + maxImagens + ' imagens.');
        event.target.value = ''; // Limpa o campo de seleção se ultrapassar o limite
        return;
    }

    if (files.length > 0) {
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const fileReader = new FileReader();

            fileReader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.width = '100px'; // Defina o tamanho da imagem
                img.style.marginRight = '10px'; // Adiciona um espaçamento

                // Cria o botão de exclusão
                const deleteButton = document.createElement('button');
                deleteButton.innerHTML = '<i class="fas fa-trash-alt"></i>'; // Ícone de lixeira
                deleteButton.type = 'button';
                deleteButton.classList.add('delete-button');
                deleteButton.style.position = 'absolute';
                //deleteButton.style.top = '5px';
                //deleteButton.style.right = '5px';
                deleteButton.onclick = function() {
                    previewDiv.removeChild(imgContainer);
                };

                // Cria um contêiner para imagem e botão de exclusão
                const imgContainer = document.createElement('div');
                imgContainer.style.position = 'relative'; // Para posicionar o botão de exclusão
                imgContainer.style.display = 'inline-block'; // Para alinhar as imagens na horizontal
                imgContainer.style.marginBottom = '10px'; // Margem entre as imagens
                imgContainer.appendChild(img);
                imgContainer.appendChild(deleteButton);

                previewDiv.appendChild(imgContainer);
            };

            fileReader.readAsDataURL(file); // Converte o arquivo em URL base64 para exibir no <img>
        }
    }
});
