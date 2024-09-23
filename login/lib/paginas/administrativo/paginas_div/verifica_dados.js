function validateForm() {
    var data_ini = document.getElementById('idata_ini').value;
    var hora_ini = document.getElementById('ihora_ini').value;
    var data_final = document.getElementById('idata_final').value;
    var hora_final = document.getElementById('ihora_final').value;

    if (!validarData(data_ini)) {
        alert("Data inicial inválida! Preencha o campo corretamente.");
        return false;
    }

    if (!validarHora(hora_ini)) {
        alert("Hora inicial inválida! Preencha o campo corretamente.");
        return false;
    }

    if (!validarData(data_final)) {
        alert("Data final inválida! Preencha o campo corretamente.");
        return false;
    }

    if (!validarHora(hora_final)) {
        alert("Hora final inválida! Preencha o campo corretamente.");
        return false;
    }

    return true;
}

function validarData(data) {
    var regexData = /^\d{2}\/\d{2}\/\d{4}$/;
    return regexData.test(data);
}

function validarHora(hora) {
    var regexHora = /^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/;
    return regexHora.test(hora);
}

function formatarData_ini(input) {
    let value = input.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
    if (value.length > 8) {
        value = value.substr(0, 8);
    }
    if (value.length > 4) {
        value = value.replace(/(\d{2})(\d{2})/, '$1/$2/');
    } else if (value.length > 2) {
        value = value.replace(/(\d{2})/, '$1/');
    } 
    input.value = value;
}
function formatarData_fim(input) {
    let value = input.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
    if (value.length > 8) {
        value = value.substr(0, 8);
    }
    if (value.length > 4) {
        value = value.replace(/(\d{2})(\d{2})/, '$1/$2/');
    } else if (value.length > 2) {
        value = value.replace(/(\d{2})/, '$1/');
    } 
    input.value = value;
}
function compararDatas() {
    var data_ini = document.getElementById('idata_ini').value;
    var data_fim = document.getElementById('idata_final').value;

    // Separando o dia, mês e ano e invertendo a ordem para "yyyy-mm-dd"
    var dataInicioParts = data_ini.split("/");
    var dataInicioFormatada = dataInicioParts[2] + "-" + dataInicioParts[1] + "-" + dataInicioParts[0];

    var dataFinalParts = data_fim.split("/");
    var dataFinalFormatada = dataFinalParts[2] + "-" + dataFinalParts[1] + "-" + dataFinalParts[0];

    var dataHoje = new Date();  // Data atual

    var dataInicio = new Date(dataInicioFormatada);
    var dataFinal = new Date(dataFinalFormatada);

    if (dataInicio < dataHoje){
        document.getElementById('idata_ini').focus();
        document.querySelector('#imgAlerta').textContent = "Data inválida! Preencha o campo corretamente.";
    } else if(dataFinal < dataInicio){
        document.getElementById('idata_final').focus();
        document.querySelector('#imgAlerta').textContent = "Data inválida! Preencha o campo corretamente.";
    } else {
        document.querySelector('#imgAlerta').textContent = ""; // Limpa a mensagem se as datas estiverem corretas
    }
}
function formatarHora(input) {
    let value = input.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos

    if (value.length > 4) {
        value = value.substr(0, 4);
    }
    if (value.length > 3) {
        value = value.replace(/(\d{2})(\d{2})/, '$1:$2');
    }
    if (value.length > 2) {
        value = value.replace(/(\d{1})(\d{2})/, '$1:$2');
    }

    input.value = value;
}
function compararHorarios() {
    var data_ini = document.getElementById('idata_ini').value;
    var data_fim = document.getElementById('idata_final').value;
    
    // Separando o dia, mês e ano e invertendo a ordem para "yyyy-mm-dd"
    var dataInicioParts = data_ini.split("/");
    var dataInicioFormatada = dataInicioParts[2] + "-" + dataInicioParts[1] + "-" + dataInicioParts[0];

    var dataFinalParts = data_fim.split("/");
    var dataFinalFormatada = dataFinalParts[2] + "-" + dataFinalParts[1] + "-" + dataFinalParts[0];
        
    var horaIni = document.getElementById('ihora_ini').value;
    var horaFim = document.getElementById('ihora_final').value;

    var dataIni = new Date(dataInicioFormatada + "T" + horaIni); // Adicionado "T" para separar data e hora
    var dataFim = new Date(dataFinalFormatada + "T" + horaFim);

    if (dataFim <= dataIni) {
        document.getElementById('ihora_final').focus();
        document.querySelector('#imgAlerta').textContent = "Horário inválido! Preencha o campo corretamente.";
    } else {
        document.querySelector('#imgAlerta').textContent = ""; // Limpa a mensagem se os horários estiverem corretos
    }
    //console.log("oii");
}




