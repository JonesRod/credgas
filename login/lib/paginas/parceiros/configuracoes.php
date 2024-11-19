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
        $sql_query = $mysqli->query("SELECT * FROM meus_parceiros WHERE id = '$id'") or die($mysqli->error);
        $parceiro = $sql_query->fetch_assoc();

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
    $id_parceiro = intval($_GET['id_parceiro']);
    $sql = $mysqli->query("SELECT * FROM meus_parceiros WHERE id = $id_parceiro") or die($mysqli->error);
    $formas = $sql->fetch_assoc();

    // Atribuir os valores das colunas às variáveis
    $horarios_funcionamento = json_decode($formas['horarios_funcionamento'], true) ?? [];
    $formas_recebimento = explode(',', $formas['formas_recebimento'] ?? '');
    $car_debito = $formas['cartao_debito'];
    $car_credito = isset($formas['Cartão credito']) ? explode(',', $formas['Cartão credito']) : [];
  
    $outras = $formas['outras_formas'];   
    $valor_minimo_pedido = $formas['valor_minimo_pedido'] ?? 0;
    $valor_min_entrega_gratis = $formas['valor_min_entrega_gratis'] ?? 0;
    $estimativa_entrega = isset($parceiro['estimativa_entrega']) ? $parceiro['estimativa_entrega'] : '';


    // Exemplo de exibição (opcional)
    /*echo "<pre>";
    print_r([
        'Horários de Funcionamento' => $horarios_funcionamento,
        'Formas de Recebimento' => $formas_recebimento,
        'Cartão Débito' => $car_debito,
        'Cartão Crédito' => $car_credito,
        'Outras' => $outras,      
        'Valor Mínimo do Pedido' => $valor_minimo_pedido,
        'Valor Mínimo para Entrega Grátis' => $valor_min_entrega_gratis
    ]);
    echo "</pre>";*/
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações de Horários</title>
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
    </style>
</head>
<body>
    <h2>Minhas Configurações</h2>

    <form  action="salvar_configuracoes.php" method="POST">
        <input type="hidden" name="id_parceiro" value="<?php echo $id_parceiro; ?>">

        <!-- Horários de Funcionamento -->
        <fieldset>
            <legend>Horários de Funcionamento</legend>
            <?php
            $dias = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'];
            foreach ($dias as $dia) {
                $abertura = $horarios_funcionamento[$dia]['abertura'] ?? '';
                $fechamento = $horarios_funcionamento[$dia]['fechamento'] ?? '';
                $almoco_inicio = $horarios_funcionamento[$dia]['almoco_inicio'] ?? '';
                $almoco_fim = $horarios_funcionamento[$dia]['almoco_fim'] ?? '';
                echo "<label>$dia:</label>";
                echo "<input type='time' name='horarios[$dia][abertura]' value='$abertura'> - ";
                echo "<input type='time' name='horarios[$dia][fechamento]' value='$fechamento'>";
                echo " | Almoço: ";
                echo "<input type='time' name='horarios[$dia][almoco_inicio]' value='$almoco_inicio'> - ";
                echo "<input type='time' name='horarios[$dia][almoco_fim]' value='$almoco_fim'>";
            }
            ?>
        </fieldset>

        <!-- Formas de Recebimento -->
        <fieldset>
            <legend>Formas de Recebimento</legend>
            <!-- Dinheiro -->
            <label>
                <input type="checkbox" name="formas_recebimento[]" value="Dinheiro" 
                <?php echo in_array('Dinheiro', $formas_recebimento) ? 'checked' : ''; ?>>
                Dinheiro
            </label>

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


            <label>
                <input type="checkbox" id="pix" name="formas_recebimento[]" value="Pix"
                <?php echo in_array('Pix', $formas_recebimento) ? 'checked' : ''; ?>>
                Pix
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

        <!-- Valor Mínimo de Pedido -->
        <fieldset>
            <legend>Valor Mínimo de Pedido</legend>
            <label>Informe o valor mínimo:</label>
            <input 
                type="text" 
                step="0.01" 
                name="valor_minimo_pedido" 
                value="<?php echo htmlspecialchars(number_format($valor_minimo_pedido, 2, ',', '.')); ?>" 
                required oninput="formatarValorMinimo(this)">
        </fieldset>

        <!-- Valor Mínimo de Pedido -->
        <fieldset>
            <legend>Valor Mínimo de compra para ter entrega Gratís</legend>
            <label>Informe o valor mínimo:</label>
            <input 
                type="text" 
                step="0.01" 
                name="valor_min_entrega_gratis" 
                value="<?php echo htmlspecialchars(number_format($valor_min_entrega_gratis, 2, ',', '.')); ?>" 
                required oninput="formatarValorminimoFrete(this)">
        </fieldset>

        <!-- Estimativa de Entrega -->
        <fieldset>
            <legend>Estimativa de Entrega</legend>
            <label>Informe o tempo máximo estimado (HH:MM):</label>
            <input 
                type="text" 
                name="estimativa_entrega" 
                value="<?php echo htmlspecialchars($estimativa_entrega); ?>" 
                required 
                placeholder="00:00"
                oninput="formatarTempoEstimativa(this)">
        </fieldset>



        <!-- Botões -->
        <div class="buttons">
            <button type="submit">Salvar</button>
            <a href="parceiro_home.php">Voltar</a>
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

    </script>
</html>
