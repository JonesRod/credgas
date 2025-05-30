* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background-color: #007BFF;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    position: relative;
}

header {
    background-color: #007BFF;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: flex-start; /* Alinha itens ao topo */
    padding: 20px;
}

header h1 {
    flex-grow: 1; /* Faz o título ocupar o espaço disponível */
    text-align: center; /* Centraliza o título horizontalmente */
    font-size: 30px; /* Tamanho padrão do título */
    line-height: 100px; /* Alinha verticalmente o título com a altura do cabeçalho */
    margin: 0; /* Remove margens padrão */
}
header .logo img {
    height: 150px; /* Aumenta o tamanho do logo */
    width: 150px; /* Ajuste proporcional ao tamanho */
    border-radius: 50%; /* Mantém o logo redondo */
    border-radius: 50%;
margin-right: 10px;
}
.logo {
display: flex;
align-items: center;
}
header .container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 20px;
    position: relative;
}
.container{
display: flex;
/*flex-direction: column;*/
align-items: center; /* Centraliza horizontalmente */
justify-content: center; /* Centraliza verticalmente */
/*left: 50vh;
height: 40vh; /* Altura total da tela */
text-align: center;
/*width: 95%;
/*padding: 10px;
margin-left: 10px;*/
} 
.nome-fantasia {
    font-size: 2.5rem; /* Tamanho maior */
    font-weight: bold;
    color: #333; /* Cor mais suave para o texto */
    line-height: 1.2;
    display: flex;
    justify-content: flex-start; /* Garante que o texto fique alinhado à esquerda */
    align-items: center;
    flex-grow: 1; /* Permite que o nome ocupe o máximo de espaço disponível ao lado da logo */
    padding-left: 15px; /* Espaço entre a logo e o nome */
    text-align: center; /* Centralizar o texto horizontalmente */
    margin: 20px 0; /* Espaçamento acima e abaixo */
    text-transform: uppercase; /* Transformar o texto para letras maiúsculas */
    letter-spacing: 1.5px; /* Espaçamento entre as letras */

}
.menu-superior-direito {
    font-size: 20px;
    display: flex;
    align-items: flex-start; /* Alinha o conteúdo no topo */
    margin-top: -10px; /* Ajuste para alinhar ao topo */
}

.menu-superior-direito span {
    margin-right: 15px; /* Espaçamento entre o nome do usuário e os ícones */
    transition: color 0.3s ease; /* Transição suave para a cor */
}
/* Efeito ao passar o mouse */
.menu-superior-direito span:hover {
    color: #f0a309; /* Muda a cor do texto ao passar o mouse */
}

.menu-superior-direito i {
    font-size: 24px; /* Aumenta o tamanho dos ícones */
    margin-left: 15px;
    transition: transform 0.3s ease, color 0.3s ease; /* Transição para o movimento e cor */
    cursor: pointer; /* Cursor de ponteiro ao passar o mouse */
}
/* Efeito ao passar o mouse */
.menu-superior-direito span:hover {
    color: #f0a309; /* Muda a cor do texto ao passar o mouse */
}

.menu-superior-direito i:hover {
    transform: translateY(-5px); /* Move o ícone para cima ao passar o mouse */
    color: #ff9d00; /* Muda a cor do ícone ao passar o mouse */
}
/* Efeito ao clicar */
.menu-superior-direito i:active {
    transform: scale(0.9); /* Diminui o tamanho do ícone ao clicar */
    color: #ff9d09; /* Muda a cor do ícone ao passar o mouse */
}
aside#menu-lateral {
    font-weight: bold; /* Aplica negrito ao texto */
    background-color: #d3d0ce;
    color: rgb(24, 8, 235);
    width: 210px; /* Largura fixa da barra lateral */
    padding: 10px;
    position: absolute; /* Mantém a barra lateral fixa */
    top: 60px; /* Ajusta a posição abaixo do cabeçalho */
    right: 20px; /* Posiciona o menu à direita */
    display: none; /* Inicialmente escondido */
    transition: all 0.3s ease; /* Transição suave */
    border-radius: 8px; /* Bordas arredondadas */
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2); /* Sombra para dar destaque */
}

aside#menu-lateral ul {
    list-style: none;
    padding: 0;
}

aside#menu-lateral ul li {
    margin: 15px 0; /* Margem entre os itens */
    font-size: 16px; /* Tamanho da fonte */
    display: flex; /* Flexbox para alinhar ícone e texto */
    align-items: center; /* Alinha verticalmente */
    transition: background-color 0.3s ease; /* Transição suave para a cor de fundo */
    border-radius: 5px; /* Bordas arredondadas */
    padding: 10px; /* Espaçamento interno */
    font-weight: bold; /* Aplica negrito ao texto */
}

/* Remove o sublinhado do link "Sair" */
#menu-lateral a {
    text-decoration: none; /* Remove o sublinhado */
    color: inherit; /* Mantém a cor do texto herdada */
    transition: color 0.3s ease; /* Suave transição de cor */
}
/* Efeito ao passar o mouse sobre o link */
#menu-lateral a:hover {
    cursor: pointer;
    color: #007BFF; /* Muda a cor ao passar o mouse */
}
/* Efeito ao passar o mouse sobre o item do menu */
aside#menu-lateral ul li:hover {
    cursor: pointer;
    background-color: rgba(0, 123, 255, 0.1); /* Cor de fundo ao passar o mouse */
}
/* Estilo para ícones */
aside#menu-lateral ul li i {
    margin-right: 10px; /* Espaçamento entre ícone e texto */
    font-size: 20px; /* Tamanho dos ícones */
    transition: transform 0.3s ease, color 0.3s ease; /* Transição para movimento e cor */
}

/* Efeito ao passar o mouse sobre o ícone */
aside#menu-lateral ul li:hover i {
    cursor: pointer;
    transform: translateY(-3px); /* Move o ícone para cima ao passar o mouse */
    color: #ffbb09; /* Muda a cor do ícone ao passar o mouse */
}
/* Efeito ao clicar em um ícone */
aside#menu-lateral ul li i:active {
    transform: scale(0.9); /* Diminui o tamanho do ícone ao clicar */
    color: #ffbb09; /* Muda a cor do ícone ao passar o mouse */
}
/* Efeitos para os spans */
aside#menu-lateral ul li span {
    transition: transform 0.3s ease, color 0.3s ease; /* Transição para movimento e cor */
}

/* Efeito ao passar o mouse sobre o span */
aside#menu-lateral ul li:hover span {
    cursor: pointer;
    transform: translateY(-3px); /* Move o ícone para cima ao passar o mouse */
    color: #bf9c44; /* Muda a cor do texto ao passar o mouse */
    /*text-decoration: underline; /* Adiciona sublinhado ao passar o mouse */
}
.notificacao-count {
    position: absolute;
    top: -8px;
    right: -1px;
    background-color: red;
    color: white;
    padding: 5px;
    border-radius: 50%;
    font-size: 12px;
    font-weight: bold;
}
/* Painel de notificações estilo semelhante ao menu lateral */
#painel-notificacoes {
    display: none;
    position: fixed;
    top: 60px; /* Ajuste conforme a altura do cabeçalho */
    right: 20px; /* Posiciona o menu à direita */
    width: 320px;
    height: 400px;
    background-color: white;
    border: 2px solid #ffb300;
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    padding: 10px;
}
/* Estilo para o ícone de notificações com o número de notificações */
.notificacoes {
    position: relative;
    display: inline-block;
}
/* Efeito de movimento no ícone de notificação e no número de notificações ao passar o mouse */
.notificacoes:hover i, 
.notificacoes:hover .notificacao-count {
    animation: moverNotificacao 0.5s ease-in-out forwards;
}
#painel-notificacoes h2 {
    margin: 0 0 10px 0;
    font-size: 18px;
    font-weight: bold;
    text-align: center;
}

#painel-notificacoes ul {
    list-style: none;
    padding: 0;
}

#painel-notificacoes li {
    padding: 10px;
    cursor: pointer;
    border-bottom: 1px solid #ddd;
}

#painel-notificacoes li:hover {
    background-color: #f0f0f0;
}

/* Definição da animação de movimento */
@keyframes moverNotificacao {
    0% {
        transform: translateY(0); /* Posição inicial */
    }
    50% {
        transform: translateY(-10px); /* Movimento para cima */
    }
    100% {
        transform: translateY(0); /* Volta à posição original */
    }
}
main {
    display: flex;
    flex-direction: column;
    height: 100vh; /* O contêiner principal ocupa a altura total da tela */
    box-sizing: border-box;
}
/* Estilos para as abas */
main .opcoes {
    background-color: #007BFF;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 0px;
    padding: auto;
}

main .tab {
    padding: 10px 20px;
    border-radius: 8px 8px 0 0; /* Bordas arredondadas só no topo, estilo de aba */
    background-color: #007BFF;
    cursor: pointer;
    font-size: 18px;
    font-weight: bold;
    text-align: center;
    transition: background-color 0.3s ease, transform 0.3s ease;
}

main .tab:hover {
    background-color: #afa791;
    color: white;
    transform: scale(1.05);
}

main .tab.active {
    background-color: #ffb300; /* Aba ativa com cor diferente */
    color: white;
    transform: scale(1.05);
}
/* Estilos para o conteúdo das abas */
.conteudo-aba {
    flex-grow: 1; /* Faz o conteúdo ocupar todo o espaço restante */
    margin-left: 2px;
    margin-right: 2px;
    margin-top: 0px;
    padding: 10px;
    border: 2px solid #ffb300;
    border-radius: 8px;
    display: none; /* Por padrão, todos os conteúdos estão escondidos */
    padding-top: 5px;
    box-sizing: border-box; /* Garante que o padding seja incluído no tamanho */
    overflow: auto; /* Para que o conteúdo role se for maior que a tela */
    background-color: #d3d0ce;

}

.btn-login {
background-color: #007bff;
color: white;
text-decoration: none;
padding: 3px 10px;
border-radius: 5px;
}

.btn-login:hover {
background-color: #0056b3;
}
.profile-dropdown {
position: relative;
}
.profile-dropdown #dropdownMenu {
display: none;
position: absolute;
top: 100%;
right: 0;
background: #fff;
box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
border-radius: 4px;
list-style: none;
padding: 10px;
}

.profile-dropdown:hover #dropdownMenu {
display: block;
}
.menu-mobile {
    background-color: #343a40;
    color: white;
    padding: 10px;
    position: fixed;
    bottom: 0;
    width: 100%;
    display: none;
    justify-content: space-around;
}
#menu-mobile i {
    text-decoration: none; /* Remove o sublinhado dos links */
    color: inherit; /* Herda a cor do item pai */
}
#menu-mobile i:hover {
    background-color: #f0f0f0; /* Efeito de hover */
    color: #007BFF; /* Cor ao passar o mouse */
}
.menu-mobile ul {
    list-style: none;  
    display: flex;
    justify-content: space-around;
    width: 100%; /* Garantir que o menu ocupe toda a largura */
}

/* Efeitos para os itens do menu mobile */
.menu-mobile ul li {
    transition: transform 0.3s ease, color 0.3s ease; /* Transição suave para movimento e cor */
}
/* Efeito ao passar o mouse sobre o item do menu */
.menu-mobile ul li:hover {
    cursor: pointer;
    transform: translateY(-3px); /* Move o item para cima ao passar o mouse */
    color: #ffbb09; /* Muda a cor do ícone ao passar o mouse */
}
.menu-mobile ul li i {
    font-size: 24px; /* Aumente o tamanho dos ícones aqui */
    margin: 0; /* Remova a margem, se necessário */
    display: block; /* Garante que o ícone seja exibido como um bloco */
    text-align: center; /* Centraliza o ícone dentro do item do menu */
    transform: scale(0.9); /* Diminui o tamanho do ícone ao clicar */
    /*color: #afa791; /* Muda a cor do ícone ao passar o mouse */
}
/* Efeito ao passar o mouse sobre o ícone */
.menu-mobile ul li:hover i {
    cursor: pointer;
    transition: transform 0.3s ease, color 0.3s ease; /* Transição suave para movimento e cor */
    color: #ffbb09; /* Muda a cor do ícone ao passar o mouse */
}
/* Faixa de Navegação */
.sub-nav {
display: flex;
justify-content: center;
align-items: center;
background-color: #f8f8f8; /* Cor de fundo suave */
padding: 10px 0;
box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Sombras sutis para destacar */
}

.sub-nav div {
font-size: 1.2rem;
font-weight: bold;
color: #333; /* Cor do texto */
margin: 0 20px; /* Espaçamento entre os itens */
cursor: pointer;
transition: all 0.3s ease; /* Suavização do efeito de hover */
}

.sub-nav div:hover {
color: #007bff; /* Cor de destaque quando o item é hover */
text-decoration: underline; /* Adiciona um sublinhado no hover */
}
.section {
margin: 40px auto;
width: 70%;
max-width: 1200px;
text-align: center;
}
main {
display: flex;
flex-direction: column;
/*height: 100vh; /* O contêiner principal ocupa a altura total da tela */
box-sizing: border-box;
align-items: center; /* Centraliza horizontalmente */
justify-content: center; /* Centraliza verticalmente */
text-align: center;
}

/* Estilos para as abas */
main .opcoes {
background-color: #007bff;
display: flex;
justify-content: center;
align-items: center;
gap: 10px;
margin-top: 0px;
padding: auto;
}

main .tab {
padding: 10px;
border-radius: 8px 8px 0 0; /* Bordas arredondadas só no topo, estilo de aba */
background-color: #27ae60;
cursor: pointer;
font-size: 20px;
font-weight: bold;
text-align: center;
transition: background-color 0.3s ease, transform 0.3s ease;
}

main .tab:hover {
background-color: #afa791;
color: white;
transform: scale(1.05);
}

main .tab.active {
background-color: #ffb300; /* Aba ativa com cor diferente */
color: white;
transform: scale(1.05);
}
/* Estilos para o conteúdo das abas */
.conteudo-aba {
flex-grow: 1; /* Faz o conteúdo ocupar todo o espaço restante */
margin-left: 2px;
margin-right: 2px;
margin-top: 0px;
padding: 10px;
border: 2px solid #ffb300;
border-radius: 8px;
display: none; /* Por padrão, todos os conteúdos estão escondidos */
padding-top: 5px;
box-sizing: border-box; /* Garante que o padding seja incluído no tamanho */
/*overflow: auto; /* Para que o conteúdo role se for maior que a tela */
background-color: #d3d0ce;
width: 100%;
text-align: center; /* Centraliza o texto */
display: flex; /* Define um layout flexível */
flex-direction: column; /* Coloca os elementos verticalmente */
align-items: center; /* Centraliza horizontalmente os itens */
justify-content: center; /* Centraliza verticalmente os itens */
height: auto;
/*min-height: 200px; /* Define uma altura mínima para centralização adequada */
/*padding: 20px; /* Adiciona espaçamento interno */
/* padding-bottom: 50px; /* Ajuste conforme o tamanho do seu menu */
}
.parceiros-carousel {
width: 100%; /* Ocupar toda a largura */
margin: 0 auto; /* Centralizar o carrossel */
display: flex; /* Flexbox para alinhar elementos */
justify-content: center; /* Centraliza o conteúdo dentro */
}
.parceiros-carousel .parceiro-card {
text-align: center;
padding: 10px;
/*background: #f9f9f9;
border: 1px solid #ddd;*/
border-radius: 60px;
/*box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);*/
margin: 10px auto; /* Centraliza e ajusta margens vertical e horizontal */
max-width: 200px; /* Define o comprimento máximo do cartão */
background-color: transparent;
}
.input{
width: 250px;
padding: 3px;
padding-left: 5px;
border-radius: 5px;
height: 20px;
border: 1px solid #ffb300;
}

.parceiros-carousel .parceiro-card img {
max-width: 120px; /* Ajuste o tamanho da logo */
height: 120px;   /* Para mantê-la circular */
margin: auto; /* Centraliza horizontalmente e adiciona espaço abaixo */
border-radius: 50%; /* Torna a imagem redonda */
display: block; /* Garante que o elemento seja tratado como bloco */
border: 2px solid #ddd; /* Borda ao redor da imagem */
}
.parceiros-carousel .parceiro-card h3 {
font-size: 1.2em;
font-weight: bold;
margin: 5px 0;
color: #333; /* Cor do texto */
}

.parceiros-carousel .parceiro-card p {
font-size: 0.9em;
color: #666; /* Cor da categoria */
margin: 5px 0 0;
}

/* Contêiner da seção de produtos */
.products {
display: flex;
flex-wrap: wrap;
gap: 10px; /* Espaçamento entre os cartões */
justify-content: center; /* Centraliza os produtos */
margin: 10px 0;

}
/* Cartão do produto */
.product-card {
background: #ffffff;
border: 1px solid #ddd;
border-radius: 10px;
width: 200px; /* Largura do cartão */
height: 420px; /* Define a altura fixa */
box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
overflow: hidden;
transition: transform 0.3s ease, box-shadow 0.3s ease;
text-align: center;
padding: 3px;
}

/* Efeito ao passar o mouse */
.product-card:hover {
transform: translateY(-5px);
box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
}

/* Imagem do produto */
.product-card img {
width: 300px;
max-width: 100%;
max-height: 250px;
height: 200px;
border-radius: 5px;
margin-bottom: 5px;
}
/* Nome do produto */
.product-card h3 {
font-size: 1.2em;
color: #333;
margin-top: 5px;
margin-bottom: 5px;
font-weight: 500;
}

/* Descrição do produto */
.product-card p {
font-size: 0.9em;
color: #333;
margin-top: 5px;
margin-bottom: 5px;
line-height: 1.4;
}
.moeda{
font-size: 1.2em;
color:#007BFF;
}

/* Preço do produto */
.product-card p:last-child {
font-size: 1em;
color: #27ae60; /* Verde para o preço */
font-weight: bold;
}
/* Botões */
.product-card .btn {
display: inline-block;
background: #27ae60; /* Cor do botão */
color: #fff;
text-decoration: none;
padding: 10px 20px;
border-radius: 5px;
margin-top: 10px;
transition: background-color 0.3s ease;
font-size: 0.9em;
}

/* Efeito ao passar o mouse no botão */
.product-card .btn:hover {
background:darkorange;
}
.descricao {
display: -webkit-box;
-webkit-line-clamp: 2; /* Limita a 2 linhas */
-webkit-box-orient: vertical;
overflow: hidden; /* Oculta o texto excedente */
text-overflow: ellipsis; /* Adiciona "..." ao final do texto cortado */
max-width: 100%; /* Define uma largura máxima para o texto */
}
.conteudo-aba h2 {
border-radius: 3px;
background-color: #fff;
text-align: left; /* Alinha o texto à esquerda */
/*margin-left: 0;   /* Garante que não há margem que afaste do lado esquerdo */
padding-left: 5px;  /* Garante que não há espaçamento interno */
}
/* Efeito hover */
.nome-fantasia:hover {
color: #007BFF; /* Muda a cor ao passar o mouse */
text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2); /* Adiciona uma leve sombra no texto */
}
    /* Responsividade para telas pequenas */
@media (max-width: 768px) {
    header h1 {
        font-size: 15px; /* Diminui o tamanho do título em telas pequenas */
        /*margin: 20px 0; /* Adiciona margem para descer o título em telas pequenas */
    }

    header .logo img {
        height: 85px; /* Diminui o tamanho do logo em telas pequenas */
        width: 85px; /* Ajuste proporcional ao tamanho */
    }

    aside#menu-lateral {
        display: none; /* Oculta a barra lateral em telas pequenas */
    }

    /* Adicionando esta linha para esconder o ícone do menu */
    .menu-superior-direito .fa-bars {
        display: none; /* Oculta o ícone do menu em telas pequenas */
    }

    .menu-mobile {
        display: flex; /* Exibe o menu mobile em telas pequenas */
    }
    main .opcoes {
        /*flex-direction: column;*/
        gap: 10px;

    }
    /* Diminui o tamanho das letras em telas menores */
    main .tab span {
        font-size: 15px; /* Ajuste conforme o necessário */
    }

    main .tab {
        width: 30%;
        max-width: 200px;
    }
    /*.sub-nav {
flex-direction: column; /* Coloca os itens em coluna em telas menores */
/*align-items: flex-start; /* Alinha os itens à esquerda */
/*padding: 15px; /* Aumenta o padding em telas menores */
/*}*/

.sub-nav div {
margin: 10px 0; /* Reduz o espaçamento entre os itens em telas menores */
text-align: left; /* Alinha os itens à esquerda */
}

.nome-fantasia {
font-size: 1.8rem; /* Tamanho reduzido para o nome fantasia */
font-weight: bold;
color: #333; /* Cor mais suave para o texto */
text-align: left; /* Alinha à esquerda para ficar mais natural ao lado da logo */
margin: 0;
line-height: 1.2;
display: flex;
justify-content: flex-start; /* Garante que o texto fique alinhado à esquerda */
align-items: center;
flex-grow: 1; /* Permite que o nome ocupe o máximo de espaço disponível ao lado da logo */
padding-left: 15px; /* Espaço entre a logo e o nome */
margin: 15px 0; /* Ajusta o espaçamento para telas pequenas */

}
    /* Cartão do produto */
.product-card {
background: #ffffff;
border: 1px solid #ddd;
border-radius: 10px;
width: 180px; /* Largura do cartão */
height: 450px; /* Define a altura fixa */
box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
overflow: hidden;
transition: transform 0.3s ease, box-shadow 0.3s ease;
text-align: center;
padding: 3px;
}
}

@media (max-width: 480px) {
.nome-fantasia {
    font-size: 1.2rem; /* Ainda menor para dispositivos móveis */
    letter-spacing: 1px; /* Reduz o espaçamento entre as letras */
}
.logo-img {
    width: 130px;
    height: 130px;
    border-radius: 50%;
    margin-right: 10px;
}
.btn-login {
    padding: 3px 5px;
    border-radius: 5px;
}

}

/* Footer */
footer {
text-align: center;
padding: 20px 0;
background-color: #333;
color: white;
margin-top: 20px;
border-radius: 10px;
}

footer .contato {
margin: 0;
}














    









    











    

    

    

