function formatarValorComPontoMilhar(valor) {
    return valor.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function formatarEntrada(input) {
    input.value = input.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
}

function formatarParcela(input) {
    input.value = input.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
}

function calcularValorParcelas() {
    var joia = parseFloat($('#ijoia').val().replace('.', '').replace(',', '.'));
    var entrada = parseFloat($('#ientrada').val().replace('.', '').replace(',', '.'));
    var parcelas = parseInt($('#iparcelas').val());

    // Verifica se a entrada é maior que o valor da joia
    if (entrada > joia) {
        alert("A entrada não pode ser maior que o valor da joia.");
        $('#ientrada').val('0');
        entrada = 0;
    }

    // Verifica se a quantidade de parcelas é menor ou igual a zero
    if (parcelas <= 0) {
        alert("A quantidade de parcelas deve ser maior que zero.");
        $('#iparcelas').val('1');
        parcelas = 1;
    }

    var valorParcela = (joia - entrada) / parcelas;
    var valorRestante = joia - entrada;

    $('#irest').val(formatarValorComPontoMilhar(valorRestante.toFixed(2)));            

    if (!isNaN(valorParcela) && valorParcela > 0) {
        $('#ivalor_parcelas').val(formatarValorComPontoMilhar(valorParcela.toFixed(2)));
    } else {
        $('#ivalor_parcelas').val('');
    }
}