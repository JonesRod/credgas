function validateForm() {
    //const arqFoto = document.getElementById('imageInput');
    var uf =document.getElementById('uf').value;
    var cidade =document.getElementById('cidade').value;
    var sem_escolha ="Escolha";

    if(uf === sem_escolha){
        document.querySelector('#msgAlerta').textContent = "Selecione o Estado onde você mora!";
        document.getElementById('uf').focus();
        //console.log(apelido);

        return false; // Impede o envio do formulário
    }
    document.querySelector('#msgAlerta').textContent = "";
    //console.log('2');

    // Aqui você pode adicionar mais validações conforme necessário
    return true; // Permite o envio do formulário
}
function formatCPF(input) {
    let value = input.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
    
    // Limita o CPF a 11 caracteres
    if (value.length > 11) {
        value = value.substr(0, 11);
    }
    
    // Aplica a formatação
    if (value.length > 9) {
        value = value.replace(/(\d{3})(\d{3})(\d{3})/, '$1.$2.$3-');
    } else if (value.length > 6) {
        value = value.replace(/(\d{3})(\d{3})/, '$1.$2');
    } else if (value.length > 3) {
        value = value.replace(/(\d{3})/, '$1.');
    }
    input.value = value; // Atualiza o valor do input
}

function verificaCpf() {
    const cpf = document.getElementById('cpf').value.replace(/\D/g, ''); // Remove formatação
    
    // Verifica se o CPF tem 11 dígitos
    if (cpf.length !== 11 || !isCpfValid(cpf)) {
        document.querySelector('#msgAlerta').textContent = "CPF inválido! Preencha o campo corretamente.";
        document.getElementById('cpf').focus();
    } else {
        document.querySelector('#msgAlerta').textContent = "";
        // Aqui você pode adicionar a verificação de existência via API, se necessário
        console.log("CPF válido! Você pode adicionar uma verificação de existência aqui.");
    }
}
function isCpfValid(cpf) {
    // Validação de CPFs com sequências repetidas
    if (/^(\d)\1+$/.test(cpf)) {
        return false; // CPF inválido se for uma sequência repetida
    }

    // Cálculo do primeiro dígito verificador
    let sum = 0;
    for (let i = 0; i < 9; i++) {
        sum += parseInt(cpf[i]) * (10 - i);
    }
    let firstDigit = (sum * 10) % 11;
    if (firstDigit === 10 || firstDigit === 11) {
        firstDigit = 0;
    }
    if (firstDigit !== parseInt(cpf[9])) {
        return false; // Primeiro dígito verificador não é válido
    }

    // Cálculo do segundo dígito verificador
    sum = 0;
    for (let i = 0; i < 10; i++) {
        sum += parseInt(cpf[i]) * (11 - i);
    }
    let secondDigit = (sum * 10) % 11;
    if (secondDigit === 10 || secondDigit === 11) {
        secondDigit = 0;
    }
    if (secondDigit !== parseInt(cpf[10])) {
        return false; // Segundo dígito verificador não é válido
    }

    return true; // CPF é válido
}
function formatarData(input) {
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

function verificaData(){
    var data_input = document.getElementById('nascimento').value;
    //const data = new Date(data_input);
    const data_inicio = '01/01/1900';
    const data_final = new Date(); // Isso pega a data e hora atuais
    const DateParts = data_inicio.split('/'); // Assumindo o formato dd/mm/yyyy
    const data_input_DateParts = data_input.split('/');

    const data_inicio_dia = parseInt(DateParts[0]);
    const data_inicio_mes = parseInt(DateParts[1]);
    const data_inicio_ano = parseInt(DateParts[2]);

    const data_inicio_convertida = new Date(data_inicio_ano, data_inicio_mes - 1, data_inicio_dia); // Mês é baseado em índices (0 a 11)
    
    var data_input_dia = parseInt(data_input_DateParts[0]);
    var data_input_mes = parseInt(data_input_DateParts[1]);
    var data_input_ano = parseInt(data_input_DateParts[2]);

    const data = new Date(data_input_ano, data_input_mes - 1, data_input_dia); // Mês é baseado em índices (0 a 11)

    const ano = new Date();
    const ano_atual = ano.getFullYear();

    //console.log(ano_atual);
    if(data_input != "") {

        if(data_input_dia < 1 || data_input_dia > 31){
            document.querySelector('#msgAlerta').textContent = "Data de nascimento invalida! Preencha o campo corretamente.";
            document.getElementById('nascimento').focus();
        }else if(data_input_mes < 1 || data_input_mes >12){
            document.querySelector('#msgAlerta').textContent = "Data de nascimento invalida! Preencha o campo corretamente.";
            document.getElementById('nascimento').focus();
        }else if(data_input_ano < 1900 || data_input_ano > ano_atual){
            document.querySelector('#msgAlerta').textContent = "Data de nascimento invalida! Preencha o campo corretamente.";
            document.getElementById('nascimento').focus();
        } 

        if(data_input.length < 10){
            document.querySelector('#msgAlerta').textContent = "Data de nascimento invalida! Preencha o campo corretamente.";
            document.getElementById('nascimento').focus();
        }
        else if (data_input.length === 10) {
            switch(data_input_mes){
                case 1: case 3: case 5: case 7: 
                case 8: case 10: case 12:
                if(data_input_dia <= 31){
                    if (data < data_inicio_convertida) {
                        document.querySelector('#msgAlerta').textContent = "Data de nascimento invalida! Preencha o campo corretamente.";
                        document.getElementById('nascimento').focus();
                        //console.log('1');
                    } else if (data.getTime() > data_final.getTime()) {
                        document.querySelector('#msgAlerta').textContent = "Data de nascimento invalida! Preencha o campo corretamente.";
                        document.getElementById('nascimento').focus();
                        //console.log('12');
                    } else {
                        document.querySelector('#msgAlerta').textContent = "";
                        //console.log(data);
                        break ;
                    }
                }else
                document.querySelector('#msgAlerta').textContent = "Data de nascimento invalida! Preencha o campo corretamente.";
                document.getElementById('nascimento').focus();
                break ;
                case 4: case 6:
                case 9: case 11:
                if(data_input_dia <= 30){
                    if (data < data_inicio_convertida) {
                        document.querySelector('#msgAlerta').textContent = "Data de nascimento invalida! Preencha o campo corretamente.";
                        document.getElementById('nascimento').focus();
                        //console.log('11');
                    } else if (data > data_final) {
                        document.querySelector('#msgAlerta').textContent = "Data de nascimento invalida! Preencha o campo corretamente.";
                        document.getElementById('nascimento').focus();
                        //console.log('22');
                    } else {
                        document.querySelector('#msgAlerta').textContent = "";
                        //console.log('23');
                        break ;
                    }
                }else
                    document.querySelector('#msgAlerta').textContent = "Data de nascimento invalida! Preencha o campo corretamente.";
                    document.getElementById('nascimento').focus();
                    break ;
                    case 2:
                    if( (data_input_ano%400 == 0) || (data_input_ano%4==0 && data_input_ano%100!=0) )
                    if( data_input_dia <= 29){
                        //console.log('111');
                        if (data < data_inicio_convertida) {
                            document.querySelector('#msgAlerta').textContent = "Data de nascimento invalida! Preencha o campo corretamente.";
                            document.getElementById('nascimento').focus();
                            //console.log('1122');
                        } else if (data > data_final) {
                            document.querySelector('#msgAlerta').textContent = "Data de nascimento invalida! Preencha o campo corretamente.";
                            document.getElementById('nascimento').focus();
                            //console.log('222');
                        } else {
                            document.querySelector('#msgAlerta').textContent = "";
                            //console.log('data');
                            break ;
                        }
                    }else{
                        document.querySelector('#msgAlerta').textContent = "Data de nascimento invalida! Preencha o campo corretamente.";
                        document.getElementById('nascimento').focus();
                    }else if( data_input_dia <= 28){
                        if (data < data_inicio_convertida) {
                            document.querySelector('#msgAlerta').textContent = "Data de nascimento invalida! Preencha o campo corretamente.";
                            document.getElementById('nascimento').focus();
                            //console.log('11222');
                        } else if (data > data_final) {
                            document.querySelector('#msgAlerta').textContent = "Data de nascimento invalida! Preencha o campo corretamente.";
                            document.getElementById('nascimento').focus();
                            //console.log(data);
                        } else {
                            document.querySelector('#msgAlerta').textContent = "";
                            break ;
                        }
                    }else
                        document.querySelector('#msgAlerta').textContent = "Data de nascimento invalida! Preencha o campo corretamente.";
                        document.getElementById('nascimento').focus();
            }
        }
    } else if(data_input == "") {
        document.querySelector('#msgAlerta').textContent = "Preencha o campo Data de nascimento.";
    }
}
function formatarCEP(input) {
    let value = input.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
    if (value.length > 8) {
        value = value.substr(0, 8);
    }
    if (value.length > 5) {
        value = value.replace(/(\d{5})/, '$1-');
    }
    input.value = value;
    //console.log(11);
}

async function buscarCidadeUF() {
    const cep = document.getElementById('cep').value.replace(/\D/g, ''); // Remove caracteres não numéricos

    if (cep.length !== 8) {
        document.querySelector('#msgAlerta').textContent = "CEP inválido! Preencha o campo corretamente.";
        document.querySelector('#cidade').value = "";
        document.querySelector('#uf').value = "---Escolha---";
        document.getElementById('cep').focus();
        return;
    }

    try {
        const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        const data = await response.json();

        if (data.erro) {
            document.querySelector('#msgAlerta').textContent = "CEP não encontrado.";
            document.querySelector('#cidade').value = "";
            document.querySelector('#uf').value = "---Escolha---";
            return;
        }

        document.querySelector('#msgAlerta').textContent = "";
        document.getElementById('cidade').value = data.localidade;
        document.getElementById('uf').value = data.uf;

    } catch (error) {
        document.querySelector('#msgAlerta').textContent = "Erro ao buscar o CEP. Tente novamente mais tarde.";
        console.error('Erro:', error);
    }
}
function formatarCelular1(input) {
    let value = input.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
    if (value.length > 11) {
        value = value.substr(0, 11);
    }
    if (value.length > 10) {
        value = value.replace(/(\d{1})(\d{1})(\d{5})/, '($1$2) $3-');
    } else if (value.length > 6) {
        value = value.replace(/(\d{1})(\d{1})(\d{4})/, '($1$2) $3-');
    } else if (value.length > 2) {
        value = value.replace(/(\d{1})(\d{1})/, '($1$2) ');
    }else if (value.length > 2) {
        value = value.replace(/(\d{1})(\d{1})/, '($1$2) ');
    }else if (value.length > 1) {
        value = value.replace(/(\d{1})/, '($1');
    }
    input.value = value;
}
function formatarCelular2(input) {
    let value = input.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
    if (value.length > 11) {
        value = value.substr(0, 11);
    }
    if (value.length > 10) {
        value = value.replace(/(\d{1})(\d{1})(\d{5})/, '($1$2) $3-');
    } else if (value.length > 6) {
        value = value.replace(/(\d{1})(\d{1})(\d{4})/, '($1$2) $3-');
    } else if (value.length > 2) {
        value = value.replace(/(\d{1})(\d{1})/, '($1$2) ');
    }else if (value.length > 2) {
        value = value.replace(/(\d{1})(\d{1})/, '($1$2) ');
    }else if (value.length > 1) {
        value = value.replace(/(\d{1})/, '($1');
    }
    input.value = value;
}
function verificaCelular1(){
    var celular =document.getElementById('celular1').value;
    //console.log(celular.length);
    if(celular.length < 15 ){
        document.querySelector('#msgAlerta').textContent = "Preencha o campo Celular corretamente!";
        document.getElementById('celular1').focus();
    }else{
        document.querySelector('#msgAlerta').textContent = "";
    }
}
function verificaCelular2(){
    var celular = document.getElementById('celular2').value;

    if(celular.length === 0){
        document.querySelector('#msgAlerta').textContent = "";
    }else if(celular.length < 15 ){
        document.querySelector('#msgAlerta').textContent = "Preencha o campo Celular corretamente!";
        document.getElementById('celular2').focus();
    }else{
        document.querySelector('#msgAlerta').textContent = "";
    }
}
function verificarAceite() {
    var checkbox = document.getElementById('aceito');
    var botaoEnviar = document.getElementById('solicitar');
    
    if (checkbox.checked) {
        botaoEnviar.disabled = false;
    } else {
        botaoEnviar.disabled = true;
    }
}