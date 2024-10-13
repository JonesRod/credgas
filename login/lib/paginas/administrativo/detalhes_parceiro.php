<?php
include('../../conexao.php');
include('../../enviarEmail.php');

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
    header("Location: ../../../../index.php");
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

// Atualizando apenas o parceiro com um id específico
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $acao = $_POST['acao'];

    if ($acao == 'aprovar') {

        // Atualizando o parceiro com um id específico para analize_inscricao = 0
        $sql_update_analize = "UPDATE meus_parceiros SET analize_inscricao = ? WHERE id = ?";
        $analize = $mysqli->prepare($sql_update_analize);
        $valor = 0; // O valor que você quer definir
        $analize->bind_param("ii", $valor, $parceiro_id); // 'ii' indica que são dois inteiros
        $analize->execute();

        // ID da notificação que você deseja atualizar
        $id_notificacao = 1;

        // Primeiro, obtenha o valor atual da notificação
        $sql_get_value = "SELECT not_inscr_parceiro FROM contador_notificacoes_admin WHERE id = ?";
        $stmt_get_value = $mysqli->prepare($sql_get_value);
        $stmt_get_value->bind_param("i", $id_notificacao);
        $stmt_get_value->execute();
        $stmt_get_value->bind_result($not_inscr_parceiro);
        $stmt_get_value->fetch();
        $stmt_get_value->close();

        // Diminui o valor atual em 1, garantindo que não fique negativo
        $valor = max(0, $not_inscr_parceiro - 1); // Não deixe o valor ficar negativo

        // Atualizando a notificação com o novo valor
        $sql_update_notif = "UPDATE contador_notificacoes_admin SET not_inscr_parceiro = ? WHERE id = ?";
        $notificacao = $mysqli->prepare($sql_update_notif);
        $notificacao->bind_param("ii", $valor, $id_notificacao); // 'ii' indica que são dois inteiros
        $notificacao->execute();
        $notificacao->close();


        $sql_update = "UPDATE meus_parceiros SET analize_aprovacao = 'APROVADO' WHERE id = ?";

        // Preparar para enviar e-mail
        $stmt = $mysqli->prepare($sql_update);
        $stmt->bind_param("i", $parceiro_id);
        $stmt->execute();

        // Preparar os dados para o e-mail
        $email = htmlspecialchars($parceiro['email']); // Certifique-se de que o e-mail do parceiro está correto
        $nomeFantasia = htmlspecialchars($parceiro['nomeFantasia']); // Nome fantasia do parceiro
        //$senha = "sua_senha_aqui"; // Altere para o método que você usa para gerar ou armazenar senhas

        // Enviar o e-mail de comunicação
        enviar_email(
            destinatario: $email,
            assunto: "Cadastro Aprovado - $nomeFantasia",
            mensagemHTML: "
            <h1>É um prazer ter você, $nomeFantasia, como parceiro!</h1>
            <p>Boas vendas!</p>
            <p>Mensagem automática. Não responda!</p>"
        );

    } elseif ($acao == 'reprovar') {

        // Atualizando o parceiro com um id específico para analize_inscricao = 0
        $sql_update_analize = "UPDATE meus_parceiros SET analize_inscricao = ? WHERE id = ?";
        $analize = $mysqli->prepare($sql_update_analize);
        $valor = 0; // O valor que você quer definir
        $analize->bind_param("ii", $valor, $parceiro_id); // 'ii' indica que são dois inteiros
        $analize->execute();

        // ID da notificação que você deseja atualizar
        $id_notificacao = 1;

        // Primeiro, obtenha o valor atual da notificação
        $sql_get_value = "SELECT not_inscr_parceiro FROM contador_notificacoes_admin WHERE id = ?";
        $stmt_get_value = $mysqli->prepare($sql_get_value);
        $stmt_get_value->bind_param("i", $id_notificacao);
        $stmt_get_value->execute();
        $stmt_get_value->bind_result($not_inscr_parceiro);
        $stmt_get_value->fetch();
        $stmt_get_value->close();

        // Diminui o valor atual em 1, garantindo que não fique negativo
        $valor = max(0, $not_inscr_parceiro - 1); // Não deixe o valor ficar negativo

        // Atualizando a notificação com o novo valor
        $sql_update_notif = "UPDATE contador_notificacoes_admin SET not_inscr_parceiro = ? WHERE id = ?";
        $notificacao = $mysqli->prepare($sql_update_notif);
        $notificacao->bind_param("ii", $valor, $id_notificacao); // 'ii' indica que são dois inteiros
        $notificacao->execute();
        $notificacao->close();

        
        $sql_update = "UPDATE meus_parceiros SET analize_aprovacao = 'REPROVADO' WHERE id = ?";

        // Preparar para enviar e-mail
        $stmt = $mysqli->prepare($sql_update);
        $stmt->bind_param("i", $parceiro_id);
        $stmt->execute();

        // Preparar os dados para o e-mail
        $email = htmlspecialchars($parceiro['email']); // Certifique-se de que o e-mail do parceiro está correto
        $nomeFantasia = htmlspecialchars($parceiro['nomeFantasia']); // Nome fantasia do parceiro
        //$senha = "sua_senha_aqui"; // Altere para o método que você usa para gerar ou armazenar senhas

        // Enviar o e-mail de comunicação
        enviar_email(
            destinatario: $email,
            assunto: "Cadastro Reprovado - $nomeFantasia",
            mensagemHTML: "
            <p>Confira no Perfil da Loja todos os dados, veja se o aquivo de imagem esta bem legivel e solicite novamente!</p>
            <p>Mensagem automática. Não responda!</p>"
        );
    }

    $stmt = $mysqli->prepare($sql_update);
    $stmt->bind_param("i", $parceiro_id);
    $stmt->execute();

    // Redireciona após a aprovação/reprovação
    header("Location: admin_home.php?id=$parceiro_id");
    exit();
}
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
    <ul>
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

    <!-- Formulário com botões de Aprovar e Reprovar -->
    <form method="POST" class="botao" onsubmit="showLoading(event)">
        <button type="submit" name="acao" value="aprovar">Aprovar</button>
        <button type="submit" name="acao" value="reprovar">Reprovar</button>
    </form>

    <?php else: ?>
        <p style="text-align:center;">Parceiro não encontrado.</p>
    <?php endif; ?>

    <!-- Fullscreen image overlay -->
    <div class="fullscreen" id="fullscreenOverlay" onclick="closeFullscreen()">
        <img id="fullscreenImage" src="" alt="Fullscreen Image">
    </div>

    <!-- Link para voltar -->
    <div style="text-align: center; margin-top: 30px;"> <!-- Aumentar a margem -->
        <a href="javascript:history.back()" class="back-link">Voltar</a>
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


</script>

</body>
</html>
