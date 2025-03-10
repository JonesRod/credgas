function formatCNPJ(input) {
    let value = input.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
    
    // Limita o CNPJ a 14 caracteres
    if (value.length > 14) {
        value = value.substr(0, 14);
    }
    
    // Aplica a formatação
    if (value.length > 12) {
        value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})/, '$1.$2.$3/$4-');
    } else if (value.length > 8) {
        value = value.replace(/(\d{2})(\d{3})(\d{3})/, '$1.$2.$3/');
    } else if (value.length > 5) {
        value = value.replace(/(\d{2})(\d{3})/, '$1.$2.');
    } else if (value.length > 2) {
        value = value.replace(/(\d{2})/, '$1.');
    }
    input.value = value; // Atualiza o valor do input
}

function verificaCNPJ() {
    const cnpj = document.getElementById('cnpj').value.replace(/\D/g, ''); // Remove formatação
    
    // Verifica se o CNPJ tem 14 dígitos
    if (cnpj.length !== 14 || !isCnpjValid(cnpj)) {
        document.querySelector('#msgAlerta').textContent = "CNPJ inválido! Preencha o campo corretamente.";
        document.getElementById('cnpj').focus();
    } else {
        document.querySelector('#msgAlerta').textContent = "";
        console.log("CNPJ válido! Você pode adicionar uma verificação de existência aqui.");
    }
}

function isCnpjValid(cnpj) {
    // Validação de CNPJs com sequências repetidas
    if (/^(\d)\1+$/.test(cnpj)) {
        return false; // CNPJ inválido se for uma sequência repetida
    }

    // Cálculo do primeiro dígito verificador
    let length = cnpj.length - 2;
    let numbers = cnpj.substring(0, length);
    let digits = cnpj.substring(length);
    let sum = 0;
    let pos = length - 7;
    
    for (let i = length; i >= 1; i--) {
        sum += numbers.charAt(length - i) * pos--;
        if (pos < 2) pos = 9;
    }
    
    let result = sum % 11 < 2 ? 0 : 11 - sum % 11;
    if (result !== parseInt(digits.charAt(0))) {
        return false; // Primeiro dígito verificador não é válido
    }

    // Cálculo do segundo dígito verificador
    length = length + 1;
    numbers = cnpj.substring(0, length);
    sum = 0;
    pos = length - 7;

    for (let i = length; i >= 1; i--) {
        sum += numbers.charAt(length - i) * pos--;
        if (pos < 2) pos = 9;
    }

    result = sum % 11 < 2 ? 0 : 11 - sum % 11;
    if (result !== parseInt(digits.charAt(1))) {
        return false; // Segundo dígito verificador não é válido
    }

    return true; // CNPJ é válido
}
async function validaCNPJ(cnpj) {
    cnpj = cnpj.replace(/[^\d]+/g, '');

    if (cnpj.length !== 14) return false;

    const response = await fetch(`https://open.cnpja.com/office/${cnpj}`);
    const data = await response.json();

    return data.status === 'OK';
}
/*document.getElementById('cadastroEmpresa').addEventListener('submit', async function(event) {
    const cnpj = document.getElementById('cnpj').value.replace(/[^\d]+/g, '');
    const isValid = await validaCNPJ(cnpj);

    if (!isValid) {
        alert('CNPJ inválido ou não encontrado!');
        event.preventDefault();
    }
});*/
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

function formatarCelular(input) {
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
    }else if (value.length = 1) {
        value = value.replace(/(\d{1})/, '($1');
    }
    input.value = value;
}
function verificaCelular1(){
    var celular =document.getElementById('telefoneComercial').value;
    //console.log(celular.length);
    if(celular.length < 15 ){
        
        document.querySelector('#msgAlerta').textContent = "Preencha o campo Celular corretamente!";
        document.getElementById('telefoneComercial').focus();
    }else{
        document.querySelector('#msgAlerta').textContent = "";
    }
}
function verificaCelular2(){
    var celular =document.getElementById('telefoneResponsavel').value;
    //console.log(celular.length);
    if(celular.length < 15 ){
        
        document.querySelector('#msgAlerta').textContent = "Preencha o campo Celular corretamente!";
        document.getElementById('telefoneResponsavel').focus();
    }else{
        document.querySelector('#msgAlerta').textContent = "";
    }
}
function verificarAceite() {
    var checkbox = document.getElementById('aceito');
    var botaoEnviar = document.getElementById('cadastrar');
    
    if (checkbox.checked) {
        botaoEnviar.disabled = false;
    } else {
        botaoEnviar.disabled = true;
    }
}
document.getElementById('arquivoEmpresa').addEventListener('change', function(event) {
    const file = event.target.files[0]; // Obtém o arquivo selecionado
    const previewDiv = document.getElementById('filePreview');
    
    if (file) {
        const fileType = file.type;
        const reader = new FileReader();

        // Limpa qualquer visualização anterior
        previewDiv.innerHTML = '';

        // Verifica se o arquivo é uma imagem PNG
        if (fileType === 'image/png') {
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = 'Pré-visualização do arquivo';
                img.style.maxWidth = '200px'; // Ajusta o tamanho da imagem
                img.style.marginTop = '10px';
                previewDiv.appendChild(img); // Exibe a imagem
            };
            reader.readAsDataURL(file); // Lê o arquivo como URL de dados
        } else if (fileType === 'application/pdf') {
            // Apenas exibe o nome do arquivo se for PDF
            const fileName = document.createElement('p');
            fileName.textContent = `Arquivo selecionado: ${file.name}`;
            previewDiv.appendChild(fileName);
        } else {
            // Se for um arquivo inválido, exibe uma mensagem de erro
            previewDiv.textContent = 'Formato de arquivo não suportado. Por favor, selecione um arquivo PDF ou PNG.';
        }
    }
});