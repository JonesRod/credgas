function validateForm() {
    const arqFoto = document.getElementById('imageInput');
    var uf =document.getElementById('iuf').value;
    var ufAtual =document.getElementById('iuf_atual').value;
    var sem_escolha ="Escolha";

    if (arqFoto.files.length === 0) {
        //alert('Por favor, preencha todos os campos.');
        document.querySelector('#imgAlerta2').textContent = "Adicione uma foto.";
        return false; // Impede o envio do formulário
    }
    if(uf === sem_escolha){
        document.querySelector('#imgAlerta2').textContent = "Selecione o Estado!";
        document.getElementById('iuf').focus();
        console.log(apelido);

        return false; // Impede o envio do formulário
    }
    if(ufAtual === sem_escolha){
        document.querySelector('#imgAlerta2').textContent = "Selecione seu Estado atual!";
        document.getElementById('iuf_atual').focus();
        return false; // Impede o envio do formulário
    }
        document.querySelector('#imgAlerta2').textContent = "";
        //console.log('2');

    // Aqui você pode adicionar mais validações conforme necessário
    return true; // Permite o envio do formulário
}
window.onload = function() { 
    //console.log('1');
    const fileInput = document.getElementById('imageInput');
    const imageElement = document.getElementById('preview');

    if (fileInput.files.length === 0) {
        imageElement.src = '../login/lib/paginas/usuarios/arquivos/9734564-default-avatar-profile-icon-of-social-media-user-vetor.jpg'; // Substitua pelo caminho da imagem padrão
    }

    fileInput.addEventListener('change', function() {
        const selectedFile = fileInput.files[0];
        if (selectedFile) {
            const reader = new FileReader();
            reader.onload = function(event) {
                imageElement.src = event.target.result;
            };
            reader.readAsDataURL(selectedFile);
        } else {
            imageElement.src ='../login/lib/paginas/usuarios/arquivos/9734564-default-avatar-profile-icon-of-social-media-user-vetor.jpg'; // Substitua pelo caminho da imagem padrão
        }
    });

};
 
function handleImageUpload(event) {
    const file = event.target.files[0];

    if (file && (file.type === 'image/png' || file.type === 'image/jpeg')) {
        const reader = new FileReader();
        reader.onload = function() {
            const preview = document.getElementById('preview');
            preview.src = reader.result;
        };
        reader.readAsDataURL(file);
        document.querySelector('#imgAlerta').textContent = "";
    } else {
        //alert('Por favor, selecione uma imagem PNG ou JPG.');
        document.getElementById('preview').src= "../login/lib/paginas/usuarios/arquivos/9734564-default-avatar-profile-icon-of-social-media-user-vetor.jpg"
        document.getElementById('imageInput').value = null;
        document.querySelector('#imgAlerta').textContent = "Por favor, selecione uma imagem PNG ou JPG.";
    }
}             

function formatCPF(input) {
    let value = input.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
    if (value.length > 11) {
        value = value.substr(0, 11);
    }
    if (value.length > 9) {
        value = value.replace(/(\d{3})(\d{3})(\d{3})/, '$1.$2.$3-');
    } else if (value.length > 6) {
        value = value.replace(/(\d{3})(\d{3})/, '$1.$2.');
    } else if (value.length > 3) {
        value = value.replace(/(\d{3})/, '$1.');
    }
    input.value = value;
}
function verificaCpf(){
    var cpf =document.getElementById('icpf').value;
    var apelido = document.getElementById('iapelido').value;

    if(apelido === ''){
        document.getElementById('iapelido').value= document.getElementById('inome').value;
    }if(cpf.length < 14){
        //console.log(cpf);
        document.querySelector('#imgAlerta').textContent = "CPF invalido! Preencha o campo corretamente.";
        document.getElementById('icpf').focus();
    }else{
        document.querySelector('#imgAlerta').textContent = "";
    }
      
}
function formatRG(input) {
    let value = input.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
    if (value.length > 11) {
        value = value.substr(0, 11);
    }
    if (value.length > 9) {
        value = value.replace(/(\d{3})(\d{3})(\d{3})/, '$1.$2.$3.');
    } else if (value.length > 6) {
        value = value.replace(/(\d{3})(\d{3})/, '$1.$2.');
    } else if (value.length > 3) {
        value = value.replace(/(\d{3})/, '$1.');
    }
    input.value = value;
}
function verificaRG(){
    var rg =document.getElementById('irg').value;
    
    if(rg.length < 4 || rg.length ===""){
        //console.log(cpf);
        document.querySelector('#imgAlerta').textContent = "Preencha o campo RG corretamnete!";
        document.getElementById('irg').focus();
    }else{
        document.querySelector('#imgAlerta').textContent = "";
    }
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
    var data_input = document.getElementById('inascimento').value;
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
            document.querySelector('#imgAlerta').textContent = "Data invalida! Preencha o campo corretamente.";
            document.getElementById('inascimento').focus();
        }else if(data_input_mes < 1 || data_input_mes >12){
            document.querySelector('#imgAlerta').textContent = "Data invalida! Preencha o campo corretamente.";
            document.getElementById('inascimento').focus();
        }else if(data_input_ano < 1900 || data_input_ano > ano_atual){
            document.querySelector('#imgAlerta').textContent = "Data invalida! Preencha o campo corretamente.";
            document.getElementById('inascimento').focus();
        } 

        if(data_input.length < 10){
            document.querySelector('#imgAlerta').textContent = "Data invalida! Preencha o campo corretamente.";
            document.getElementById('inascimento').focus();
        }
        else if (data_input.length === 10) {
            switch(data_input_mes){
                case 1: case 3: case 5: case 7: 
                case 8: case 10: case 12:
                if(data_input_dia <= 31){
                    if (data < data_inicio_convertida) {
                        document.querySelector('#imgAlerta').textContent = "Data invalida! Preencha o campo corretamente.";
                        document.getElementById('inascimento').focus();
                        //console.log('1');
                    } else if (data.getTime() > data_final.getTime()) {
                        document.querySelector('#imgAlerta').textContent = "Data invalida! Preencha o campo corretamente.";
                        document.getElementById('inascimento').focus();
                        //console.log('12');
                    } else {
                        document.querySelector('#imgAlerta').textContent = "";
                        //console.log(data);
                        break ;
                    }
                }else
                document.querySelector('#imgAlerta').textContent = "Data invalida! Preencha o campo corretamente.";
                document.getElementById('inascimento').focus();
                break ;
                case 4: case 6:
                case 9: case 11:
                if(data_input_dia <= 30){
                    if (data < data_inicio_convertida) {
                        document.querySelector('#imgAlerta').textContent = "Data invalida! Preencha o campo corretamente.";
                        document.getElementById('inascimento').focus();
                        //console.log('11');
                    } else if (data > data_final) {
                        document.querySelector('#imgAlerta').textContent = "Data invalida! Preencha o campo corretamente.";
                        document.getElementById('inascimento').focus();
                        //console.log('22');
                    } else {
                        document.querySelector('#imgAlerta').textContent = "";
                        //console.log('23');
                        break ;
                    }
                }else
                    document.querySelector('#imgAlerta').textContent = "Data invalida! Preencha o campo corretamente.";
                    document.getElementById('inascimento').focus();
                    break ;
                    case 2:
                    if( (data_input_ano%400 == 0) || (data_input_ano%4==0 && data_input_ano%100!=0) )
                    if( data_input_dia <= 29){
                        //console.log('111');
                        if (data < data_inicio_convertida) {
                            document.querySelector('#imgAlerta').textContent = "Data invalida! Preencha o campo corretamente.";
                            document.getElementById('inascimento').focus();
                            //console.log('1122');
                        } else if (data > data_final) {
                            document.querySelector('#imgAlerta').textContent = "Data invalida! Preencha o campo corretamente.";
                            document.getElementById('inascimento').focus();
                            //console.log('222');
                        } else {
                            document.querySelector('#imgAlerta').textContent = "";
                            //console.log('data');
                            break ;
                        }
                    }else{
                        document.querySelector('#imgAlerta').textContent = "Data invalida! Preencha o campo corretamente.";
                        document.getElementById('inascimento').focus();
                    }else if( data_input_dia <= 28){
                        if (data < data_inicio_convertida) {
                            document.querySelector('#imgAlerta').textContent = "Data invalida! Preencha o campo corretamente.";
                            document.getElementById('inascimento').focus();
                            //console.log('11222');
                        } else if (data > data_final) {
                            document.querySelector('#imgAlerta').textContent = "Data invalida! Preencha o campo corretamente.";
                            document.getElementById('inascimento').focus();
                            //console.log(data);
                        } else {
                            document.querySelector('#imgAlerta').textContent = "";
                            break ;
                        }
                    }else
                        document.querySelector('#imgAlerta').textContent = "Data invalida! Preencha o campo corretamente.";
                        document.getElementById('inascimento').focus();
            }
        }
    } else if(data_input == "") {
        document.querySelector('#imgAlerta').textContent = "Preencha o campo Data de nascimento.";
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
}
async function fetchCityByCEP() {
    const cep = document.getElementById('icep').value.replace(/\D/g, ''); // Remove caracteres não numéricos

    if (cep.length !== 8) {
        //alert('CEP inválido.');
        document.querySelector('#imgAlerta').textContent = "CEP invalido! Preencha o campo corretamente.";
        document.querySelector('#icid_atual').value = "";
        document.getElementById('icep').focus();
        return;
    }

    const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
    const data = await response.json();
    document.querySelector('#imgAlerta').textContent = "";

    if (data.erro) {
        //alert('CEP não encontrado.');
        document.querySelector('#imgAlerta').textContent = "CEP está incorretamente.";
        document.querySelector('#icid_atual').value = "";
        //document.getElementById('icep').focus();
        return;
    }
    document.querySelector('#imgAlerta').textContent = "";
    document.getElementById('icid_atual').value = data.localidade;
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
function verificaCelular1(){
    var celular =document.getElementById('icelular1').value;
    //console.log(celular.length);
    if(celular.length < 11 ){
        
        document.querySelector('#imgAlerta').textContent = "Preencha o campo Celular corretamente!";
        document.getElementById('icelular1').focus();
    }else{
        document.querySelector('#imgAlerta').textContent = "";
    }
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
function verificaCelular2(){
    var celular = document.getElementById('icelular2').value;

    if(celular.length === 0){
       // console.log(celular.value );
        document.querySelector('#imgAlerta2').textContent = "";
        //document.getElementById('icelular2').focus();
    }else if(celular.length < 14 ){
        //console.log(celular.value );
        document.querySelector('#imgAlerta2').textContent = "Preencha o campo Celular corretamente!";
        document.getElementById('icelular2').focus();
    }else{
        document.querySelector('#imgAlerta').textContent = "";
    }
}
function verificarAceite() {
    var checkbox = document.getElementById('iaceito');
    var botaoEnviar = document.getElementById('solicitar');
    
    if (checkbox.checked) {
        botaoEnviar.disabled = false;
    } else {
        botaoEnviar.disabled = true;
    }
}