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
    $termos_cliente_vista = $dados['termos_cliente_vista'];
    $termos_cliente_crediario = $dados['termos_cliente_crediario'];
    $termos_parceiro = $dados['termos_parceiro'];
    $termos_privacidade = $dados['termos_privacidade'];

    $taxa_padrao = str_replace('.', ',', $dados['taxa_padrao']);
    $formas_recebimento = explode(',', $dados['formas_recebimento'] ?? '');
    $car_debito = isset($dados['cartoes_debito']) ? explode(',', $dados['cartoes_debito']) : [];
    $car_credito = isset($dados['Cartoes_credito']) ? explode(',', $dados['Cartoes_credito']) : [];
    $pix = str_replace('.', ',', $dados['taxa_pix']);
    $taxa_crediario = str_replace('.', ',', $dados['taxa_crediario']);
    $taxa_outros = str_replace('.', ',', $dados['taxa_outros']);
    $outras = str_replace('.', ',', $dados['outras_formas']);

    $multa_inclu_spc = str_replace('.', ',', $dados['multa_inclu_spc']);
    $juro_inclu_spc = str_replace('.', ',', $dados['juro_inclu_spc']);
    $valor_dias_cli_fiel = str_replace('.', ',', $dados['valor_dias_cli_fiel']);
    $valor_dias_cli_pontual = str_replace('.', ',', $dados['valor_dias_cli_pontual']);
    $bonus_indicacao = str_replace('.', ',', $dados['bonus_indicacao']);
    $bonus_aniversariante = str_replace('.', ',', $dados['bonus_aniversariante']);

    // Consulta para carregar as categorias do banco
    $sql = $mysqli->query("SELECT * FROM categorias ORDER BY categorias ASC") or die($mysqli->error);
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
        input[type="number"],
        input[type="text"]  {
            margin-right: 10px;
            border-radius: 5px;
            height: 20px;
            padding: 3px;
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
        textarea{
            width: 99%;
            height: 200px;
            border-radius: 5px;
            padding: 5px;
        }

    </style>
</head>
<body>
    <h2>Configurações</h2>

    <form  action="salvar_configuracoes.php" method="POST">
        <input type="hidden" name="id_admin" value="<?php echo $id_admin; ?>">

        <label for="idmc">Idade minima para se cadastrar:</label>
        <input type="number" id="idmc" name="idmc" required value="<?php echo $idade_min_cadastro;?>">

        <label for="idsc">Idade minima para solicitar Crediário:</label>
        <input type="number" id="idsc" name="idsc" required value="<?php echo $idade_min_crediario;?>">

        <label for="tc">Termos para ser cliente:</label>
        <textarea name="tc" id="tc" required><?php echo $termos_cliente_vista;?></textarea>

        <label for="tcr">Termos para o crediário:</label>
        <textarea name="tcr" id="tcr" required><?php echo $termos_cliente_crediario;?></textarea>

        <label for="tp">Termos para ser parceiro:</label>
        <textarea name="tp" id="tp" required><?php echo $termos_parceiro;?></textarea>

        <label for="tpr">Termos de privacidade:</label>
        <textarea name="tpr" id="tpr" required><?php echo $termos_privacidade;?></textarea>

        <!-- Categorias de Produtos -->
        <fieldset>
            <legend>Categorias de Produtos</legend>
            <div class="buttons">
                <!-- Botão para gerenciar cartões -->
                <button type="button" class="addcartao" onclick="window.location.href='adicionar_categorias.php?id=<?php echo $id; ?>'">Adicionar Nova Categoria</button>
            </div>

            <!-- Lista de categorias -->
            <ul id="listaCategorias">
                <?php
                if ($sql->num_rows > 0) {
                    while ($categoria = $sql->fetch_assoc()) {
                        echo '<li>' . htmlspecialchars($categoria['categorias']) . '</li>';
                    }
                } else {
                    echo '<li>Nenhuma categoria encontrada.</li>';
                }
                ?>
            </ul>        

        </fieldset>

        <!-- Formas de Recebimento -->
        <fieldset>
            <legend>Formas de Recebimento</legend>
            <div class="buttons">
                <!-- Botão para gerenciar cartões -->
                <button type="button" class="addcartao" onclick="window.location.href='lista_cartoes.php?id=<?php echo $id; ?>'">Gerenciar Cartões</button>
            </div>

            <label>
                <input type="checkbox" id="cartao_debito" name="formas_recebimento[]" value="Cartão de Débito"
                <?php echo in_array('Cartão de Débito', $formas_recebimento) ? 'checked' : ''; ?>>
                Cartão de Débito
            </label>
            <div id="opcoes_debito" class="card-options" style="<?php echo in_array('Cartão de Débito', $formas_recebimento) ? 'display: block;' : 'display: none;'; ?>">
                <?php
                    // Recupera os cartões salvos na coluna 'cartao_debito' do banco
                    $car_debito = isset($dados['cartoes_debito']) ? explode(',', $dados['cartoes_debito']) : [];

                    // Exibe os checkboxes para os cartões disponíveis
                    foreach ($lista_cartoes as $cartao) : 
                ?>
                <label>
                    <input type="checkbox" name="cartoes_debito[]" value="<?php echo htmlspecialchars($cartao['nome']); ?>"
                    <?php echo in_array($cartao['nome'], $car_debito) ? 'checked' : ''; ?>>
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
                    $cartoes_credito_salvos = isset($dados['cartoes_credito']) ? explode(',', $dados['cartoes_credito']) : [];

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
                <input type="checkbox" id="crediario" name="formas_recebimento[]" value="Crediario"
                <?php echo in_array('Crediario', $formas_recebimento) ? 'checked' : ''; ?>>
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
            <label for="taxa_padrao">Taxa Padrão %:</label>
            <input type="text" id="taxa_padrao" name="taxa_padrao"  value="<?php echo $taxa_padrao; ?>" oninput="formatarValor(this)">

            <!--<label for="">Taxa Cartão de Débito:</label>
            <input type="text">

            <label for="">Taxa Cartão de Crédito:</label>
            <input type="text">-->

            <label for="taxa_pix">Pix %:</label>
            <input type="text" id="taxa_pix" name="taxa_pix" value="<?php echo $pix;  ?>" oninput="formatarValor(this)">

            <label for="taxa_crediario">Taxa Crediário %:</label>
            <input type="text" id="taxa_crediario" name="taxa_crediario" value="<?php echo $taxa_crediario; ?>" oninput="formatarValor(this)">

            <label for="taxa_outros">Taxa Outros %:</label>
            <input type="text" id="taxa_outros" name="taxa_outros" value="<?php echo $taxa_outros ?>" oninput="formatarValor(this)">
        </fieldset>

        <!-- Multas e juros para o crediario -->
        <fieldset>
            <legend>Configure as Multas e Juros</legend>
            <label for="">Dias para inclusão ao SPC:</label>
            <input type="number" id="dias_inclu_spc" name="dias_inclu_spc" value="<?php echo $dados['dias_inclu_spc'];; ?>">

            <label for="multa_inclu_spc">Multa SPC R$:</label>
            <input type="text" id="multa_inclu_spc" name="multa_inclu_spc" value="<?php echo $multa_inclu_spc; ?>" oninput="formatarValor(this)">

            <label for="juro_inclu_spc">Juro sobre o Valor incluido no SPC %:</label>
            <input type="text" id="juro_inclu_spc" name="juro_inclu_spc" value="<?php echo $juro_inclu_spc; ?>" oninput="formatarValor(this)">
        </fieldset>

        <!-- Desconto cliente fiel -->
        <fieldset>
            <legend>Configure o desconto para cliente fiel</legend>

            <label for="dias_cli_fiel">Dias da ultima compras para ganhar desconto:</label>
            <input type="number" id="dias_cli_fiel" name="dias_cli_fiel" value="<?php echo $dados['dias_cli_fiel']; ?>">

            <label for="">Valor do Desconto %:</label>
            <input type="text" id="valor_dias_cli_fiel" name="valor_dias_cli_fiel" value="<?php echo $valor_dias_cli_fiel; ?>" oninput="formatarValor(this)">
        </fieldset>

        <!-- Desconto cliente pontual -->
        <fieldset>
            <legend>Configure os descontos para clientes Pontuais</legend>
                <label for="dias_cli_pontual">Dias para ganhar o desconto:</label>
                <input type="number" id="dias_cli_pontual" name="dias_cli_pontual" value="<?php echo $dados['dias_cli_pontual']; ?>">

                <label for="valor_dias_cli_pontual">Valor do Desconto %:</label>
                <input type="text" id="valor_dias_cli_pontual" name="valor_dias_cli_pontual" value="<?php echo $valor_dias_cli_pontual; ?>" oninput="formatarValor(this)">                
        </fieldset>

        <!-- Bonus de indicação-->
        <fieldset>
            <legend>Configure o Bonus de indicação</legend>

                <label for="bonus_indicacao">Valor do Bonus R$:</label>
                <input type="text" id="bonus_indicacao" name="bonus_indicacao" value="<?php echo $bonus_indicacao; ?>" oninput="formatarValor(this)">                
        </fieldset>

        <!-- Bonus para aniversariantes-->
        <fieldset>
            <legend>Configure o Bonus para aniversáriantes</legend>

                <label for="bonus_aniversariante">Valor do Bonus R$:</label>
                <input type="text" id="bonus_aniversariante" name="bonus_aniversariante" value="<?php echo $bonus_aniversariante; ?>" oninput="formatarValor(this)">                
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
        function formatarValor(input) {
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

    </script>
</html>
