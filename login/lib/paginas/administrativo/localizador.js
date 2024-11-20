// Chama a geolocalização assim que a página é carregada
window.onload = function() {
    obterLocalizacao();
}

function obterLocalizacao() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(mostrarPosicao, mostrarErro, {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 0
        });
    } else {
        document.getElementById("status-localizacao").textContent = "Geolocalização não é suportada por este navegador.";
    }
}

function mostrarPosicao(posicao) {
    const lat = posicao.coords.latitude;
    const lon = posicao.coords.longitude;
    //document.getElementById("status-localizacao").textContent = `Localização: Latitude ${lat}, Longitude ${lon}`;
    obterEndereco(lat, lon); // Chama a função para fazer a geocodificação reversa
}

async function obterEndereco(lat, lon) {
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18&addressdetails=1`);
        const data = await response.json();
        
        const endereco = data.address;
        const cep = endereco.postcode || 'Não disponível';
        const cidade = endereco.city || endereco.town || endereco.village || 'Não disponível';
        const estado = endereco.state || 'Não disponível';
        const uf = getUF(estado); // Mapeia o nome do estado para o código da UF

        // Exibe o CEP, cidade e estado
        document.getElementById("cep").value = `${cep}`;
        document.getElementById("cidade").value = `${cidade}`;
        document.getElementById("uf").value = `${estado}`;

        // Atualiza o campo de seleção para o estado
        const ufSelect = document.getElementById('uf');
        ufSelect.value = uf; // Define o valor do select para a UF correspondente

    } catch (error) {
        console.error('Erro ao obter o endereço:', error);
        document.getElementById("status-localizacao").textContent = "Não foi possível obter o endereço.";
    }
}
// Função para mapear o nome do estado para a sigla correspondente
function getUF(estado) {
    const estados = {
        "Acre": "AC",
        "Alagoas": "AL",
        "Amapá": "AP",
        "Amazonas": "AM",
        "Bahia": "BA",
        "Ceará": "CE",
        "Distrito Federal": "DF",
        "Espírito Santo": "ES",
        "Goiás": "GO",
        "Maranhão": "MA",
        "Mato Grosso": "MT",
        "Mato Grosso do Sul": "MS",
        "Minas Gerais": "MG",
        "Pará": "PA",
        "Paraíba": "PB",
        "Paraná": "PR",
        "Pernambuco": "PE",
        "Piauí": "PI",
        "Rio de Janeiro": "RJ",
        "Rio Grande do Norte": "RN",
        "Rio Grande do Sul": "RS",
        "Rondônia": "RO",
        "Roraima": "RR",
        "Santa Catarina": "SC",
        "São Paulo": "SP",
        "Sergipe": "SE",
        "Tocantins": "TO",
        "São Paulo": "SP",
        "Sergipe": "SE",
        "Tocantins": "TO"
    };

    return estados[estado] || 'Escolha'; // Retorna a sigla ou 'Escolha' se não encontrado
}
function mostrarErro(error) {
    switch (error.code) {
        case error.PERMISSION_DENIED:
            document.getElementById("status-localizacao").textContent = "Usuário negou a solicitação de Geolocalização.";
            break;
        case error.POSITION_UNAVAILABLE:
            document.getElementById("status-localizacao").textContent = "Informações de localização indisponíveis.";
            break;
        case error.TIMEOUT:
            document.getElementById("status-localizacao").textContent = "A solicitação para obter a localização expirou.";
            break;
        case error.UNKNOWN_ERROR:
            document.getElementById("status-localizacao").textContent = "Ocorreu um erro desconhecido.";
            break;
    }
}
