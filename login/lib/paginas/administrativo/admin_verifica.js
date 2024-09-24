function validateForm() {
    const arqLogo = document.getElementById('imageInput');
    const imageElementAtual = document.getElementById('ilogo');
    const imageElement = document.getElementById('ilogoNova');
    var uf = document.getElementById('iuf').value;
    //var ufAtual =document.getElementById('iuf_atual').value;
    var sem_escolha ="Escolha";

    if (arqLogo.files.length === 0 && imageElementAtual.src =='' && imageElement.src=='') {
        alert('Por favor, adicione uma logo.');
        document.querySelector('#imsgAlerta').textContent = "Adicione uma logo.1";
        return false; // Impede o envio do formulário
    }
    /*if (arqLogo.files.length === 0) {
        //alert('Por favor, preencha todos os campos.');
        document.querySelector('#imsgAlerta').textContent = "Adicione uma logo.2";
        return false; // Impede o envio do formulário
    }*/
    if(uf === sem_escolha){
        document.querySelector('#imsgAlerta').textContent = "Selecione o Estado!";
        document.getElementById('iuf').focus();
        console.log(apelido);

        return false; // Impede o envio do formulário
    }
        document.querySelector('#imsgAlerta').textContent = "";
        //console.log('2');

    // Aqui você pode adicionar mais validações conforme necessário
    return true; // Permite o envio do formulário
}
function imgLogo(event) {
    const file = event.target.files[0];
    var minhaLogo = document.getElementById('ilogo');
    var novalogo = document.getElementById('ilogoNova');
    const inputElement = document.getElementById('imageInput');
    //console.log('oi');
    if (file && (file.type === 'image/png' || file.type === 'image/jpeg')) {
        
        const reader = new FileReader();
        reader.onload = function() {
            const novalogo = document.getElementById('ilogoNova');
            novalogo.src = reader.result;
        };
        reader.readAsDataURL(file);
        //document.getElementById('ifoto').type = hidden;
        minhaLogo.style.display = 'none';
        novalogo.style.display = 'block';
        document.querySelector('#imsgAlerta').textContent = "";
    } 

    if (inputElement.files.length === 0) {
        novalogo.style.display = 'none';
        minhaLogo.style.display = 'block';

    } 
}   
function formataCNPJ(input) {
    let value = input.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
    if (value.length > 14) {
        value = value.substr(0, 14);
    }if (value.length > 12) {
        value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})/, '$1.$2.$3/$4-');
    } else if (value.length > 8) {
        value = value.replace(/(\d{2})(\d{3})(\d{3})/, '$1.$2.$3/');
    } else if (value.length > 5) {
        value = value.replace(/(\d{2})(\d{3})/, '$1.$2.');
    } else if (value.length > 2) {
        value = value.replace(/(\d{2})/, '$1.');
    }
    input.value = value;
}
function verificaCnpj() {
    var cnpj = document.getElementById('icnpj').value;

    if(cnpj.length < 18) {
        console.log(cnpj.length);
        document.querySelector('#imsgAlerta').textContent = "CNPJ invalido! Preencha o campo corretamente.";
        document.getElementById('icnpj').focus();
    }else{
        document.querySelector('#imsgAlerta').textContent = "";
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
        document.querySelector('#imsgAlerta').textContent = "CEP invalido! Preencha o campo corretamente.";
        document.querySelector('#icidade').value = "";
        document.getElementById('icep').focus();
        return;
    }

    const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
    const data = await response.json();
    document.querySelector('#imsgAlerta').textContent = "";

    if (data.erro) {
        //alert('CEP não encontrado.');
        document.querySelector('#imsgAlerta').textContent = "CEP está incorretamente.";
        document.querySelector('#icidade').value = "";
        //document.getElementById('icep').focus();
        return;
    }
    document.querySelector('#imsgAlerta').textContent = "";
    document.getElementById('icidade').value = data.localidade;
}

function baixarArq_estatuto() {
    // Obter o conteúdo da textarea
    /*var conteudo = document.getElementById("iEst").value;

    // Criar um link de download
    var link = document.createElement('a');
    link.href = 'data:text/plain;charset=utf-8,' + encodeURIComponent(conteudo);
    link.download = 'Estatuto_interno.txt';
    link.click();*/
    //console.log('oi');
    
    var nomeArquivo = document.getElementById('iEst').value;
    var link = document.createElement("a");
    link.href = nomeArquivo;
    link.download = nomeArquivo;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
function baixarArq_regimento() {
    // Obter o conteúdo da textarea
    /*var conteudo = document.getElementById("iEst").value;

    // Criar um link de download
    var link = document.createElement('a');
    link.href = 'data:text/plain;charset=utf-8,' + encodeURIComponent(conteudo);
    link.download = 'Estatuto_interno.txt';
    link.click();*/
    //console.log('oi');
    
    var nomeArquivo = document.getElementById('iReg').value;
    var link = document.createElement("a");
    link.href = nomeArquivo;
    link.download = nomeArquivo;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
// Função para perguntar se deseja salvar as alterações
function perguntarSalvar() {
    var resposta = confirm("Deseja salvar as informações alteradas?");
    if (resposta) {
        // Adicione aqui o código para salvar as informações
        document.getElementById("meuFormulario").submit();
        alert("As informações foram salvas!");
        window.location.href = 'admin_home.php';
    }else{
        window.location.href = 'admin_home.php';
    }
}

/*window.addEventListener('popstate', function(event) {
    history.pushState(null, document.title, location.href);
  });

window.onbeforeunload = function() {
    return "Tem certeza que deseja sair sem salvar?";
};

document.addEventListener('keydown', function(event) {
    if (event.key === "ArrowLeft") { // Verifica se a tecla pressionada é a seta à esquerda
        var confirmacao = confirm("Tem certeza que deseja sair sem salvar?");
        if (confirmacao) {
            window.onbeforeunload = null; // Remove a mensagem de confirmação
        } else {
            event.preventDefault(); // Previne o comportamento padrão do navegador (navegar para trás)
        }
    }
});*/

