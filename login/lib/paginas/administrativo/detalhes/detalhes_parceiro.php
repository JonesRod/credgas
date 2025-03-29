<?php
include('../../../conexao.php');
include('../../../enviarEmail.php');

if (!isset($_SESSION)) {
    session_start();
}

if (isset($_SESSION['id'])) {
    $id = $_SESSION['id'];
    $sql_query = $mysqli->query("SELECT * FROM meus_clientes WHERE id = '$id'") or die($mysqli->$error);
    $usuario = $sql_query->fetch_assoc();
} else {
    // Se não houver uma sessão de usuário, redirecione para a página de login
    session_unset();
    session_destroy();
    header("Location: ../../../../../index.php");
    exit();
}

// Pega o ID do parceiro da URL
$parceiro_id = $_GET['id'];

// Construa a consulta SQL para buscar os dados do parceiro específico
$sql_query = "SELECT * FROM meus_parceiros WHERE id = ?" or die($mysqli->$error);

// Prepare e execute a consulta
$stmt = $mysqli->prepare($sql_query);
$stmt->bind_param("i", $parceiro_id);
$stmt->execute();
$result = $stmt->get_result();
$parceiro = $result->fetch_assoc();

// Verifica se o valor 'aberto_fechado' está presente nos dados e atribui 'Aberto' ou 'Fechado'
$statusLoja =  $parceiro['aberto_fechado_manual'];

// Verifica se o valor 'aberto_fechado' está presente nos dados e atribui 'Aberto' ou 'Fechado'
$statusLojaaut =  $parceiro['aberto_fechado_aut'];

// Simulação de dados recuperados do banco de dados
$horarios_json = $parceiro['horarios_funcionamento'];

// Converte JSON para array associativo
$horarios = json_decode($horarios_json, true);

// Obtém o dia da semana atual
$dias_semana = ["Domingo", "Segunda", "Terça", "Quarta", "Quinta", "Sexta", "Sábado"];
$dia_atual = $dias_semana[date('w')]; // Retorna o nome do dia da semana

// Obtém a hora atual
$hora_atual = date('H:i');

// Lógica para definir o status automaticamente
$statusAutomatico = "Fechado"; // Padrão caso os horários não sejam encontrados

// Verifica se o dia atual está na tabela de horários
if (isset($horarios[$dia_atual])) {
    $abertura = $horarios[$dia_atual]['abertura'];
    $fechamento = $horarios[$dia_atual]['fechamento'];
    $almoco_inicio = $horarios[$dia_atual]['almoco_inicio'];
    $almoco_fim = $horarios[$dia_atual]['almoco_fim'];

    // Verifica se está dentro do horário de funcionamento
    if ($hora_atual >= $abertura && $hora_atual <= $fechamento) {
        // Verifica se está no horário de almoço (se houver)
        if (!empty($almoco_inicio) && !empty($almoco_fim) && $hora_atual >= $almoco_inicio && $hora_atual <= $almoco_fim) {
            $statusAutomatico = "Fechado para almoço";
        } else {
            $statusAutomatico = "Aberto";
        }
    }
}

// Define o status final com base no modo ativado
if ($statusLojaaut === "Ativado") {
    $status = $statusAutomatico;
} else {
    $status = $statusLoja;
}

// Define a cor do status
$cor_status = ($status === "Aberto") ? "green" : "red";

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Parceiro</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
        }
        #Div-logo{
            text-align: center;
        }
        #img{
            width: 300px;
            height: 300px;
            border-radius: 50%;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            padding: 12px;
            margin-bottom: 4px;
            border-bottom: 1px solid #ddd;
            background: #fff;
            border-radius: 5px;
        }

        li strong {
            display: inline-block;
            width: 200px;
        }

        .image-preview {
            margin: 20px 0;
            text-align: center;
        }

        .image-preview img {
            max-width: 500px;
            max-height: 500px;
            border: 2px solid #007BFF;
            border-radius: 5px;
            display: block;
            margin: 0 auto;
            cursor: pointer; /* Change cursor to pointer on hover */
        }

        /* Fullscreen overlay */
        .fullscreen {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .fullscreen img {
            max-width: 100%; /* Aumentar para 95% */
            max-height: 100%; /* Aumentar para 95% */

        }

        .botao {
            margin-top: 20px;
            text-align: center;
        }

        button {
            padding: 10px 15px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 0 10px;
        }

        button:hover {
            background-color: #0056b3;
        }
        /* Estilo para a tela de carregamento */
        #loading {
            display: none; /* Oculto inicialmente */
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.5); /* Fundo semitransparente */
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Bolinha girando */
        #loading .spinner {
            border: 8px solid #f3f3f3; /* Cor de fundo da borda */
            border-top: 8px solid #3498db; /* Cor da borda superior (a parte visível girando) */
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

 
            #conteudo-produtos{
                background-color: #fff;
            }

            /* Estilização da tabela de parceiros e produtos */

            .tabela-produtos{
                width: 100%;
                border-collapse: collapse;
                border-radius: 8px;
                background-color: #fff;
                margin: 0; /* Remove as margens */
                padding: 0; /* Remove qualquer padding interno */
            }
            /* Ajuste para as células da tabela */
            .tabela-produtos th, 
            .tabela-produtos td{
                padding: 5px;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }
             
            .tabela-produtos th{
                background-color: #f4f4f4;
                font-weight: bold;
                border-radius: 0px;
            }

            .tabela-produtos .detalhes-link{
                color: #007bff;
                text-decoration: none;
                font-weight: bold;
            }

            .tabela-produtos .detalhes-link:hover {
                text-decoration: underline;
            }
            .imagem {
                width: 80px;
                height: 80px;
                object-fit: cover;
                border-radius: 10px;
                border: 1px solid #ddd;
            }
            /* Estilo dos filtros de produtos */
/* Estilo dos filtros de produtos */
.filtros-produtos{
    margin-bottom: 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.filtros-produtos label {
    display: flex;
    align-items: center;
    font-size: 14px;
    cursor: pointer;
}
 
.filtros-produtos input[type="checkbox"] {
    margin-right: 5px;
}
/* Caixa de seleção estilizada */ 
.filtros-produtos select{
    padding: 8px;
    border-radius: 5px;
    border: 1px solid #ccc;
    font-size: 14px;
    background-color: #f9f9f9;
    width: 200px;
}

.filtrar {
    background-color: #007bff; /* Cor de fundo azul */
    color: #fff; /* Cor do texto */
    border: none; /* Sem borda */
    border-radius: 8px; /* Bordas arredondadas */
    padding: 5px 20px; /* Espaçamento interno */
    font-size: 15px; /* Tamanho da fonte */
    cursor: pointer; /* Cursor de ponteiro */
    transition: background-color 0.3s ease; /* Transição suave para o hover */
}

.filtrar:hover {
    background-color: #0056b3; /* Cor de fundo mais escura no hover */
}

.filtrar:active {
    background-color: #003f7f; /* Cor mais escura quando pressionado */
}

@media (max-width: 768px) {
    /*.filtros-produtos*/ .filtrar {
        width: 100%;
        font-size: 14px;
        padding: 12px;
    }
}



        /* Responsividade */
        @media (max-width: 768px) {
            li {
                padding: 10px;
            }

            h1 {
                font-size: 20px;
            }
        }

        @media (max-width: 480px) {
            h1 {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>

<h1>Detalhes do Parceiro</h1>

<?php if ($parceiro): ?>
    <div id="Div-logo">
        <input id="id" type="hidden" value="<?php echo htmlspecialchars($parceiro['id']); ?>">
        <img id="img" src="<?php echo '../../parceiros/arquivos/'.htmlspecialchars($parceiro['logo']); ?>" alt="sem imagem">
    </div>
    <ul>
        <li><strong>Status:</strong> 
            <?php echo ($parceiro['status'] == '1') ? 'Ativo' : 'Inativo'; ?>
        </li>
        <li><strong>Status de Funcionamento: </strong>
            <span id="status" name="status" style="color: <?= $cor_status ?>;">
                <?= htmlspecialchars($status) ?>
            </span>
        </li>
        <li><strong>Data de Cadastro:</strong> <?php echo htmlspecialchars(date("d/m/Y", strtotime($parceiro['data_cadastro']))); ?></li>
        <li><strong>RAZÃO:</strong> <?php echo htmlspecialchars($parceiro['razao']); ?></li>
        <li><strong>Nome Fantasia:</strong> <?php echo htmlspecialchars($parceiro['nomeFantasia']); ?></li>
        <li><strong>CNPJ:</strong> <?php echo htmlspecialchars($parceiro['cnpj']); ?></li>
        <li><strong>Inscrição Estadual:</strong> <?php echo htmlspecialchars($parceiro['inscricaoEstadual']); ?></li>
        <li><strong>Categoria:</strong> <?php echo htmlspecialchars($parceiro['categoria']); ?></li>
        
        <div class="image-preview">
            <li><strong>Anexo Comprovante:</strong></li>
            <?php 
            if (!empty($parceiro['anexo_comprovante'])) {
                echo '<img src="../parceiros/arquivos/' . htmlspecialchars($parceiro['anexo_comprovante']) . '" alt="Comprovante" onclick="openFullscreen(this)">';
            } else {
                echo '<p>Nenhum anexo disponível</p>';
            }
            ?>
        </div>

        <li><strong>Telefone Comercial:</strong> <?php echo htmlspecialchars($parceiro['telefoneComercial']); ?></li>
        <li><strong>Telefone do Responsável:</strong> <?php echo htmlspecialchars($parceiro['telefoneResponsavel']); ?></li>
        <li><strong>Email:</strong> <?php echo htmlspecialchars($parceiro['email']); ?></li>
        <li><strong>CEP:</strong> <?php echo htmlspecialchars($parceiro['cep']); ?></li>
        <li><strong>Estado:</strong> <?php echo htmlspecialchars($parceiro['estado']); ?></li>
        <li><strong>Cidade:</strong> <?php echo htmlspecialchars($parceiro['cidade']); ?></li>
        <li><strong>RUA/AV:</strong> <?php echo htmlspecialchars($parceiro['endereco']); ?></li>
        <li><strong>Número:</strong> <?php echo htmlspecialchars($parceiro['numero']); ?></li>
        <li><strong>Bairro:</strong> <?php echo htmlspecialchars($parceiro['bairro']); ?></li>
    </ul>

    <!-- Tela de carregamento -->
    <div id="loading" style="display: none;">
        <div class="spinner"></div>
    </div>
    <h1>Lista de produtos</h1>
    <div id="conteudo-produtos" class="conteudo-aba" style="display: block;">
        <div class="filtros-produtos">
            
            <label for="categoria">
                Categoria:
                <select name="categoria" id="categoria">
                    <option value="">Todas as Categorias</option>
                    <?php
                    $queryCategorias = "SELECT id, categorias FROM categorias ORDER BY categorias ASC";
                    $resultCategorias = $mysqli->query($queryCategorias);

                    if ($resultCategorias->num_rows > 0) {
                        while ($categoria = $resultCategorias->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($categoria['categorias']) . "'>" . htmlspecialchars($categoria['categorias']) . "</option>";
                        }                                
                    } else {
                        echo "<option value=''>Nenhuma categoria encontrada</option>";
                    }
                    ?>
                </select>
            </label>

            <!-- Filtros com checkboxes -->
            <label for="ativoPro">
                <input type="checkbox" name="statusPro[]" value="ativoPro" id="ativoPro"> Ativo
            </label>
            <label for="inativoPro">
                <input type="checkbox" name="statusPro[]" value="inativoPro" id="inativoPro"> Inativo
            </label>
            <label for="crediarioVende">
                <input type="checkbox" name="statusPro[]" value="crediarioVende" id="crediarioVende"> Crediário
            </label>
            <label for="oculto">
                <input type="checkbox" name="statusPro[]" value="oculto" id="oculto"> Oculto
            </label>
            <label for="mais-vendidos">
                <input type="checkbox" name="statusPro[]" value="mais-vendidos" id="mais-vendidos"> Mais Vendidos
            </label>
            <label for="novidades">
                <input type="checkbox" name="statusPro[]" value="novidades" id="novidades"> Novidades
            </label>
            <label for="promocao">
                <input type="checkbox" name="statusPro[]" value="promocao" id="promocao"> Promoção
            </label>
            <label for="frete-gratis">
                <input type="checkbox" name="statusPro[]" value="frete-gratis" id="frete-gratis"> Frete Grátis
            </label>

            <button class="filtrar" type="button" onclick="filtrarProdutos()">
                🔍 Filtrar
            </button>
            <?php
                include('../../../conexao.php');

                // Consulta SQL para carregar os produtos
                $sql = "SELECT id_produto, data, imagens, nome_produto, categoria FROM produtos WHERE id_parceiro = $parceiro_id ORDER BY data DESC";
                $result = $mysqli->query($sql);


                // Conta o número total de produtos carregados
                $totalProdutos = $result->num_rows;

            ?>
            <span id="total-produtos" style="margin-left: 10px; margin-top: 10px; font-weight: bold;">Total de produtos: <?php echo $totalProdutos; ?></span>
        </div>

        <table class="tabela-produtos">
            <thead>
                <tr>
                    <th>Data de Cadastro</th>
                    <th>Imagem</th>
                    <th>Produto</th>
                    <th>Categoria</th>
                    <th>Detalhes</th>
                </tr>
            </thead>

            <tbody id="produtos-tabela">
                <?php
                /*include('../../conexao.php');

                // Consulta SQL para carregar os produtos
                $sql = "SELECT id_produto, data, imagens, nome_produto, categoria FROM produtos ORDER BY data DESC";
                $result = $mysqli->query($sql);*/

                if ($result->num_rows > 0) {
                    while ($produto = $result->fetch_assoc()) {
                        // Obtém a primeira imagem
                        $imagens = explode(',', $produto['imagens']);
                        $primeiraImagem = $imagens[0];

                        echo "<tr>";
                        echo "<td>" . date('d/m/Y', strtotime($produto['data'])) . "</td>";
                        echo "<td><img src='../../parceiros/produtos/img_produtos/" . $primeiraImagem . "' alt='Imagem do Produto' class='imagem'></td>";
                        echo "<td>" . htmlspecialchars($produto['nome_produto']) . "</td>";
                        echo "<td>" . htmlspecialchars($produto['categoria']) . "</td>";
                        echo "<td><a href='detalhes_produto.php?id_parceiro=" . $parceiro_id . '&id_produto=' . $produto['id_produto'] . "' class='detalhes-link'>Ver Detalhes</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Nenhum produto encontrado.</td></tr>";
                }
                ?>
            </tbody>
        </table>

    </div> 

    <!-- Formulário com botões de Aprovar e Reprovar -->
    <!--<form method="POST" class="botao" onsubmit="showLoading(event)">
        <button type="submit" name="acao" value="aprovar">Aprovar</button>
        <button type="submit" name="acao" value="reprovar">Reprovar</button>
    </form>-->

    <?php else: ?>
        <p style="text-align:center;">Parceiro não encontrado.</p>
    <?php endif; ?>

    <!-- Fullscreen image overlay -->
    <div class="fullscreen" id="fullscreenOverlay" onclick="closeFullscreen()">
        <img id="fullscreenImage" src="" alt="Fullscreen Image">
    </div>

    <!-- Link para voltar -->
    <div style="text-align: center; margin-top: 30px;"> <!-- Aumentar a margem -->
        <a href="../admin_home.php" class="back-link">Voltar</a>
    </div>


<script>
    function openFullscreen(img) {
        var overlay = document.getElementById('fullscreenOverlay');
        var fullscreenImage = document.getElementById('fullscreenImage');

        fullscreenImage.src = img.src; // Set the image source to the clicked image
        overlay.style.display = 'flex'; // Show the overlay
    }

    function closeFullscreen() {
        var overlay = document.getElementById('fullscreenOverlay');
        overlay.style.display = 'none'; // Hide the overlay
    }

    function showLoading(event) {
        // Exibe o elemento de carregamento quando o formulário for submetido
        document.getElementById('loading').style.display = 'flex';
    }

        function filtrarProdutos() {
            // Obtém os valores dos filtros
            const id = document.getElementById('id').value;
            const categoria = document.getElementById('categoria').value;
            const status = Array.from(document.querySelectorAll('input[name="statusPro[]"]:checked'))
                .map(checkbox => checkbox.value);

            // Cria uma requisição AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'filtrar_produtos_parceiro.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            // Quando a requisição for concluída, atualiza a tabela
            xhr.onload = function () {
                if (xhr.status === 200) {
                    document.getElementById('produtos-tabela').innerHTML = xhr.responseText;

                    // Conta o número de produtos carregados
                    const linhasProdutos = document.querySelectorAll('#produtos-tabela tr');
                    const totalProdutos = Array.from(linhasProdutos).filter(linha => !linha.querySelector('.msg')).length;
                    document.getElementById('total-produtos').textContent = `Total de produtos: ${totalProdutos}`;
                }
            };

            // Envia os dados dos filtros para o servidor
            xhr.send('id=' + id + '&categoria=' + categoria + '&statusPro=' + JSON.stringify(status));

        }
</script>

</body>
</html>
