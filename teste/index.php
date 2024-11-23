<?php
include('login/lib/conexao.php');

if(!isset($_SESSION)) {
    session_start();
}

// Verifica se o usuário está logado
$usuarioLogado = isset($_SESSION['id']);
//$id_conf = '1';


$dados = $mysqli->query("SELECT * FROM config_admin WHERE logo != '' ORDER BY data_alteracao DESC LIMIT 1") or die($mysqli->error);

$dadosEscolhido = $dados->fetch_assoc();


$nomeFantasia = $dadosEscolhido['nomeFantasia'];

// Carrega a logo
if (isset($dadosEscolhido['logo'])) {
    $logo = $dadosEscolhido['logo'];
    if ($logo == '') {
        $logo = 'login/lib/paginas/arquivos_fixos/imagem_credgas.jpg';
    } else {
        $logo = 'login/lib/paginas/administrativo/arquivos/' . $logo;
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Loja</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="cadastro_inicial/localizador.js" defer></script>
    <link rel="stylesheet" href="index.css">
    <script>
        // Função para simular o clique no botão ao carregar a página
        window.onload = function() {
            setTimeout(function() {
                var cep = document.getElementById('cep').value;
                if (cep) {
                    document.getElementById('buscarButton').click();
                }
            }, 5000); // 2000 milissegundos = 2 segundos
        };
    </script>
    <style>

    </style>
</head>
<body>

    <!-- Header -->
    <header>
        <div class="container">
            <div class="logo">
                <img src="<?php if(isset($logo)) echo $logo; ?>" alt="Logo" class="logo-img">
                <h1 class="nome-fantasia">
                    <?php 
                    if (!empty($nomeFantasia)) {
                        echo htmlspecialchars($nomeFantasia);
                    } else {
                        echo "Nome Fantasia Indisponível";
                    }
                    ?>
                </h1>
            </div>
            <div class="user-area">
                <?php if ($usuarioLogado): ?>
                    <span>Bem-vindo, <strong><?php echo htmlspecialchars($_SESSION['nome_completo']); ?></strong></span>
                    <i class="fas fa-bell"></i>
                    <i class="fas fa-shopping-cart"></i>
                <?php else: ?>
                    <span>Bem-vindo, Visitante</span>
                    <a href="login/lib/login.php" class="btn-login">Entrar</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <form method="POST">
        <label for="cep">CEP:</label>
        <input required name="cep" id="cep" type="text" maxlength="9" oninput="formatarCEP(this)" onblur="buscarCidadeUF()">
        <input type="text" id="cidade" name="cidade" >
        <input type="text" id="uf" name="uf" >
        <button type="submit" id="buscarButton">Buscar</button>
    </form>

    <!-- Faixa de navegação -->
    <div class="sub-nav">
        <div>Início</div>
        <div>Promoções</div>
        <div>Novidades</div>
        <div>Frete Grátis</div>
    </div>

    <?php

        // Inicializa a variável de produtos para evitar erros de escopo
        $result_produtos = null;

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cep'])) {
            // Sanitiza o valor do CEP
            $cep = $mysqli->real_escape_string(trim($_POST['cep']));

            // Consulta para buscar parceiros pelo CEP
            $sql_parceiros = "SELECT * FROM meus_parceiros WHERE cep = '$cep'";
            $result_parceiros = $mysqli->query($sql_parceiros) or die($mysqli->error);

            if ($result_parceiros->num_rows > 0) {
                while ($parceiro = $result_parceiros->fetch_assoc()) {
                    $id_parceiro = $parceiro['id'];
                    
                    // Consulta para carregar produtos do parceiro
                    $sql_produtos = "SELECT * FROM produtos WHERE id_parceiro = $id_parceiro"; 
                    $result_produtos = $mysqli->query($sql_produtos) or die($mysqli->error);
                }
            } else {
                echo "<p>Nenhum parceiro ativo encontrado para o CEP informado.</p>";
            }
        }
    ?>

    <!-- Produtos -->
    <div class="container">
        <h2>Produtos em Destaque</h2>
        <div class="products">
            <?php if ($result_produtos && $result_produtos->num_rows > 0): ?>
                <?php while ($produto = $result_produtos->fetch_assoc()): ?>
                    <div class="product-card">
                        <?php
                            // Supondo que a coluna 'imagens' contém os nomes das imagens separados por vírgulas
                            $imagens = !empty($produto['imagens']) ? explode(',', $produto['imagens']) : [];
                            $primeira_imagem = $imagens[0] ?? 'placeholder.jpg'; // Usa uma imagem padrão se não houver imagens
                        ?>
                        <img src="login/lib/paginas/parceiros/produtos/img_produtos/<?php echo htmlspecialchars($primeira_imagem); ?>" alt="<?php echo htmlspecialchars($produto['nome_produto']); ?>">
                        <h3><?php echo htmlspecialchars($produto['nome_produto']); ?></h3>
                        <p><?php echo htmlspecialchars($produto['descricao_produto']); ?></p>
                        <p>R$ <?php echo number_format($produto['valor_produto'], 2, ',', '.'); ?></p>
                        <a href="detalhes_produto.php?id=<?php echo $produto['id_produto']; ?>" class="btn">Detalhes</a>

                        <!-- Verifica se o usuário está logado para permitir a compra -->
                        <?php if (isset($usuarioLogado) && $usuarioLogado): ?>
                            <a href="#" class="btn">Comprar</a>
                        <?php else: ?>
                            <a href="login.php" class="btn">Faça login para comprar</a>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Não há produtos em destaque no momento.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Script para mostrar/ocultar o menu suspenso ao clicar no ícone de perfil
        document.getElementById("profileIcon").addEventListener("click", function() {
            var dropdownMenu = document.getElementById("dropdownMenu");
            dropdownMenu.classList.toggle("show"); // Alterna a classe "show" para exibir ou ocultar o menu
        });

        // Fechar o menu suspenso ao clicar fora dele
        window.onclick = function(event) {
            if (!event.target.matches('#profileIcon')) {
                var dropdowns = document.getElementsByClassName("dropdown-menu");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        };
    </script>
    <script>
        // Função para simular o clique no botão ao carregar a página
        window.onload = function() {
            setTimeout(function() {
                var cep = document.getElementById('cep').value;
                if (cep) {
                    document.getElementById('buscarButton').click();
                }
            }, 5000); // 2000 milissegundos = 2 segundos
        };
    </script>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Minha Loja - Todos os direitos reservados</p>
        <div class="contato">
            <p><strong>Contato:</strong></p>
            <p>Email: contato@minhaloja.com.br | Telefone: (11) 1234-5678</p>
        </div>
    </footer>

</body>
</html>

