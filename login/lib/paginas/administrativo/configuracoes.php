<?php
    include('../../conexao.php');

    // Inicia a sessão
    if (!isset($_SESSION)) {
        session_start(); 
    }

    // Verifica se o usuário está logado
    if (isset($_SESSION['id'])) {
        $id = $_SESSION['id'];

        // Consulta para buscar o parceiro
        $sql_query = $mysqli->query("SELECT * FROM config_admin WHERE id_cliente = '$id'") or die($mysqli->error);
        $admin = $sql_query->fetch_assoc();

    } else {
        session_unset();
        session_destroy(); 
        header("Location: ../../../../index.php");
        exit();
    }

    // Obtém a lista de cartões do banco de dados
    $sql_cartoes = "SELECT * FROM cartoes";
    $result_cartoes = $mysqli->query($sql_cartoes);
    
    // Verifica se a consulta foi bem-sucedida
    if (!$result_cartoes) {
        die("Erro na consulta SQL: " . $mysqli->error);
    }
    
    // Processa os resultados
    $lista_cartoes = [];
    if ($result_cartoes->num_rows > 0) {
        while ($cartao = $result_cartoes->fetch_assoc()) {
            $lista_cartoes[] = $cartao;
        }
        // Libera os resultados após o processamento
        $result_cartoes->free();
    } else {
        //echo "Nenhum cartão encontrado.";
    }
        
    // Obter os dados existentes do parceiro
    $id_admin = intval($_GET['id_admin']);

    $sql_query = $mysqli->query("SELECT * FROM config_admin 
        WHERE idade_min_cadastro != '' 
        ORDER BY data_alteracao DESC 
        LIMIT 1
    ") or die($mysqli->error);

    $dados = $sql_query->fetch_assoc();

    // Atribuir os valores das colunas às variáveis
    $idade_min_cadastro = $dados['idade_min_cadastro'];
    $idade_min_crediario = $dados['idade_min_crediario'];
    $formas_recebimento = explode(',', $dados['formas_recebimento'] ?? '');
    $car_debito = isset($dados['cartoes_debito']) ? explode(',', $dados['cartoes_debito']) : [];
    $car_credito = isset($dados['Cartoes_credito']) ? explode(',', $dados['Cartoes_credito']) : [];
  
    $outras = $dados['outras_formas'];   
    /*$valor_minimo_pedido = $dados['valor_minimo_pedido'] ?? 0;
    $valor_min_entrega_gratis = $dados['valor_min_entrega_gratis'] ?? 0;
    $estimativa_entrega = isset($parceiro['estimativa_entrega']) ? $parceiro['estimativa_entrega'] : '';*/

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
        }
        h2 {
            text-align: center; /* Centraliza o texto */
            margin-bottom: 20px;
        }
        form {
            max-width: 600px;
            margin: auto;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
        }
        label {
            display: block;
            margin-top: 10px;
        }
        input[type="time"],
        input[type="number"] {
            margin-right: 10px;
        }
        fieldset {
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 15px;
            padding: 10px;
        }
        legend {
            font-weight: bold;
            padding: 0 10px;
        }
        .buttons {
            text-align: center;
        }
        .buttons button, .buttons a {
            padding: 10px 20px;
            margin: 5px;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .buttons button {
            background-color: #28a745;
            color: #fff;
        }
        .buttons a {
            background-color: #dc3545;
            color: #fff;
        }
        .card-options {
            display: none; /* Escondido por padrão */
            margin-left: 20px;
            margin-top: 10px;
        }
        /* Estilização do popup */
        .popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .popup-content {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }

    </style>
</head>
<body>
    <h2>Configurações</h2>

    <form  action="salvar_configuracoes.php" method="POST">
        <input type="hidden" name="id_admin" value="<?php echo $id_admin; ?>">

        <label for="">Idade minima para se cadastrar:</label>
        <input type="number">

        <label for="">Idade minima para solicitar Crediário:</label>
        <input type="number">

        <label for="">Termos para ser cliente:</label>
        <textarea name="" id=""></textarea>

        <label for="">Termos para o crediário:</label>
        <textarea name="" id=""></textarea>

        <label for="">Termos para ser parceiro:</label>
        <textarea name="" id=""></textarea>

        <label for="">Termos de privacidade:</label>
        <textarea name="" id=""></textarea>

        <!-- Formas de Recebimento -->
        <fieldset>
            <legend>Formas de Recebimento</legend>

            <!-- Botão para gerenciar cartões -->
            <button type="button" onclick="window.location.href='lista_cartoes.php?id=<?php echo $id; ?>'">Gerenciar Cartões</button>

            <!-- Popup para gerenciar cartões -->
            <div id="popupCartoes" class="popup">
                <div class="popup-content">
                    <h3>Gerenciar Cartões</h3>
                    <form id="formCartoes" action="salvar_cartoes.php" method="POST">
                        <label for="novoCartao">Adicionar Cartão:</label>
                        <input type="text" id="novoCartao" name="novoCartao" required placeholder="Nome do Cartão">
                        <button type="submit">Adicionar</button>
                    </form>
                    
                    <!-- Lista de cartões existentes -->
                    <div id="listaCartoes">
                        <?php if (!empty($lista_cartoes)) : ?>
                            <?php foreach ($lista_cartoes as $cartao) : ?>
                                <div>
                                    <span><?php echo htmlspecialchars($cartao['nome']); ?></span>
                                    <button type="button" onclick="excluirCartao('<?php echo $cartao['id']; ?>')">Excluir</button>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p>Nenhum cartão encontrado.</p>
                        <?php endif; ?>
                    </div>
                    <button type="button" onclick="fecharPopup()">Fechar</button>
                </div>
            </div>

            <label>
                <input type="checkbox" id="cartao_debito" name="formas_recebimento[]" value="Cartão de Débito"
                <?php echo in_array('Cartão de Débito', $formas_recebimento) ? 'checked' : ''; ?>>
                Cartão de Débito
            </label>
            <div id="opcoes_debito" class="card-options" style="<?php echo in_array('Cartão de Débito', $formas_recebimento) ? 'display: block;' : 'display: none;'; ?>">
                <?php
                    // Recupera os cartões salvos na coluna 'cartao_debito' do banco
                    $cartoes_debito_salvos = isset($parceiro['cartao_debito']) ? explode(',', $parceiro['cartao_debito']) : [];

                    // Exibe os checkboxes para os cartões disponíveis
                    foreach ($lista_cartoes as $cartao) : 
                ?>
                <label>
                    <input type="checkbox" name="cartoes_debito[]" value="<?php echo htmlspecialchars($cartao['nome']); ?>"
                    <?php echo in_array($cartao['nome'], $cartoes_debito_salvos) ? 'checked' : ''; ?>>
                    <?php echo htmlspecialchars($cartao['nome']); ?>
                </label>
                <?php endforeach; ?>
            </div>

            <label>
                <input type="checkbox" id="cartao_credito" name="formas_recebimento[]" value="Cartão de Crédito"
                <?php echo in_array('Cartão de Crédito', $formas_recebimento) ? 'checked' : ''; ?>>
                Cartão de Crédito
            </label>

            <div id="opcoes_credito" class="card-options" style="<?php echo in_array('Cartão de Crédito', $formas_recebimento) ? 'display: block;' : 'display: none;'; ?>">
                <?php
                    // Recupera os cartões salvos na coluna 'cartao_credito' do banco
                    $cartoes_credito_salvos = isset($parceiro['cartao_credito']) ? explode(',', $parceiro['cartao_credito']) : [];

                    // Exibe os checkboxes para os cartões disponíveis
                    foreach ($lista_cartoes as $cartao) : 
                ?>
                <label>
                    <input type="checkbox" name="cartoes_credito[]" value="<?php echo htmlspecialchars($cartao['nome']); ?>"
                    <?php echo in_array($cartao['nome'], $cartoes_credito_salvos) ? 'checked' : ''; ?>>
                    <?php echo htmlspecialchars($cartao['nome']); ?>
                </label>
                <?php endforeach; ?>
            </div>

            <!-- Dinheiro -->
            <label>
                <input type="checkbox" name="formas_recebimento[]" value="Dinheiro" 
                <?php echo in_array('Dinheiro', $formas_recebimento) ? 'checked' : ''; ?>>
                Dinheiro
            </label>

            <label>
                <input type="checkbox" id="pix" name="formas_recebimento[]" value="Pix"
                <?php echo in_array('Pix', $formas_recebimento) ? 'checked' : ''; ?>>
                Pix
            </label>

            <label>
                <input type="checkbox" id="crediario" name="formas_recebimento[]" value="Pix"
                <?php echo in_array('crediario', $formas_recebimento) ? 'checked' : ''; ?>>
                Crediário
            </label>

            <!-- Adiciona a opção de "Outros" após o Pix -->
            <label>
                <input type="checkbox" id="forma_outros" name="formas_recebimento[]" value="Outras"
                <?php echo in_array('Outras', $formas_recebimento) ? 'checked' : ''; ?>>
                Outros
            </label>

            <!-- Campo de entrada escondido inicialmente, mostrado se "Outras" estiver selecionado -->
            <input type="text" id="descricao_outros_forma" name="descricao_outros_forma" 
                value="<?php echo htmlspecialchars($outras ?? ''); ?>" 
                placeholder="Vale, Cheque, ..." 
                style="display: <?php echo in_array('Outras', $formas_recebimento) ? 'block' : 'none'; ?>; 
            margin-top: 10px; 
            width: 95%;">

        </fieldset>

        <!-- Taxa -->
        <fieldset>
            <legend>Configure as taxas</legend>
            <label for="">Taxa Padrão:</label>
            <input type="text">

            <label for="">Taxa Cartão de Débito:</label>
            <input type="text">

            <label for="">Taxa Cartão de Crédito:</label>
            <input type="text">

            <label for="">Pix:</label>
            <input type="text">

            <label for="">Taxa Crediário:</label>
            <input type="text">

            <label for="">Taxa Outros:</label>
            <input type="text">
        </fieldset>

        <!-- Multas e juros para o crediario -->
        <fieldset>
            <legend>Configure as Multas e Juros</legend>
            <label for="">Dias para inclusão ao SPC:</label>
            <input type="number">

            <label for="">Multa SPC R$:</label>
            <input type="text">

            <label for="">Juros sobre o Valor incluido no SPC %:</label>
            <input type="text">
        </fieldset>

        <!-- Desconto cliente fiel -->
        <fieldset>
            <legend>Configure o desconto para cliente fiel</legend>

            <label for="">Dias da ultima compras para ganhar desconto:</label>
            <input type="number">

            <label for="">Valor do Desconto R$:</label>
            <input type="text">
        </fieldset>

        <!-- Desconto cliente pontual -->
        <fieldset>
            <legend>Configure os descontos para clientes Pontuais</legend>
                <fieldset>
                    <label for="">Dias para ganhar o desconto:</label>
                    <input type="number">

                    <label for="">Valor do Desconto R$:</label>
                    <input type="text">                
                </fieldset>

                <fieldset>
                    <label for="">Dias para ganhar o desconto:</label>
                    <input type="number">

                    <label for="">Valor do Desconto R$:</label>
                    <input type="text">                
                </fieldset>
        </fieldset>

        <!-- Bonus de indicação-->
        <fieldset>
            <legend>Configure o Bonus de indicação</legend>

                <label for="">Valor do Bonus R$:</label>
                <input type="text">                
        </fieldset>

        <!-- Bonus para aniversariantes-->
        <fieldset>
            <legend>Configure o Bonus para aniversáriantes</legend>

                <label for="">Dias da ultima compra para canhar o Bonus:</label>
                <input type="number">

                <label for="">Valor do Bonus R$:</label>
                <input type="text">                
        </fieldset>

        <!-- Botões -->
        <div class="buttons">
            <button type="submit">Salvar</button>
            <a href="admin_home.php">Voltar</a>
        </div>
    </form>
</body>
    <script>
        // Mostrar ou esconder opções de cartões
        const cartaoCredito = document.getElementById('cartao_credito');
        const opcoesCredito = document.getElementById('opcoes_credito');
        const cartaoDebito = document.getElementById('cartao_debito');
        const opcoesDebito = document.getElementById('opcoes_debito');

        cartaoCredito.addEventListener('change', () => {
            opcoesCredito.style.display = cartaoCredito.checked ? 'block' : 'none';
        });

        cartaoDebito.addEventListener('change', () => {
            opcoesDebito.style.display = cartaoDebito.checked ? 'block' : 'none';
        });

        // Obtém os elementos de "outros" e o campo de descrição
        const checkboxFormaOutros = document.getElementById('forma_outros');
        const inputDescricaoForma = document.getElementById('descricao_outros_forma');

        // Adiciona um evento de clique ao checkbox "Outros"
        checkboxFormaOutros.addEventListener('change', function() {
            if (checkboxFormaOutros.checked) {
                inputDescricaoForma.style.display = 'block'; // Mostra o campo de entrada
            } else {
                inputDescricaoForma.style.display = 'none'; // Esconde o campo de entrada
                inputDescricaoForma.value = ''; // Limpa o valor do campo
            }
        });

        // Função para formatar o valor digitado no campo "valor_produto"
        function formatarValorMinimo(input) {
            let valor = input.value.replace(/\D/g, '');  // Remove todos os caracteres não numéricos
            valor = (valor / 100).toFixed(2);           // Divide por 100 para ajustar para formato de decimal (0.00)

            valor = valor.replace('.', ',');            // Substitui o ponto pela vírgula
            valor = valor.replace(/\B(?=(\d{3})+(?!\d))/g, ".");  // Adiciona os pontos para separar os milhares

            input.value = valor;                        // Atualiza o valor no campo

        }
        // Função para formatar o valor digitado no campo "valor_produto"
        function formatarValorminimoFrete(input) {
            let valor = input.value.replace(/\D/g, '');  // Remove todos os caracteres não numéricos
            valor = (valor / 100).toFixed(2);           // Divide por 100 para ajustar para formato de decimal (0.00)

            valor = valor.replace('.', ',');            // Substitui o ponto pela vírgula
            valor = valor.replace(/\B(?=(\d{3})+(?!\d))/g, ".");  // Adiciona os pontos para separar os milhares

            input.value = valor;                        // Atualiza o valor no campo

        }
        function formatarTempoEstimativa(input) {
            let valor = input.value.replace(/\D/g, ''); // Remove caracteres não numéricos
            
            // Preenche com zeros à esquerda se necessário
            while (valor.length < 4) {
                valor = '0' + valor; 
            }
            
            // Garante que o valor não ultrapasse 4 dígitos
            if (valor.length > 4) {
                valor = valor.slice(-4);
            }

            // Divide em horas e minutos (HH:MM)
            valor = valor.replace(/(\d{2})(\d{2})/, '$1:$2');
            
            input.value = valor;
        }


        function abrirPopup() {
            document.getElementById('popupCartoes').style.display = 'flex';
        }

        function fecharPopup() {
            document.getElementById('popupCartoes').style.display = 'none';
        }

        function excluirCartao(cartao) {
            if (confirm('Tem certeza que deseja excluir este cartão?')) {
                // Envia o cartão a ser excluído via AJAX
                fetch('excluir_cartao.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ cartao: cartao })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        alert('Cartão excluído com sucesso!');
                        // Atualiza a lista de cartões
                        location.reload();
                    } else {
                        alert('Erro ao excluir o cartão.');
                    }
                });
            }
        }


    </script>
</html>
