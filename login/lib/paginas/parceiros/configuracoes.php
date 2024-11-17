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

    // Exemplo de exibição (opcional)
    echo "<pre>";
    print_r([
        'Horários de Funcionamento' => $horarios_funcionamento,
        'Formas de Recebimento' => $formas_recebimento,
        'Cartão Débito' => $car_debito,
        'Cartão Crédito' => $car_credito,
        'Outras' => $outras,      
        'Valor Mínimo do Pedido' => $valor_minimo_pedido,
        'Valor Mínimo para Entrega Grátis' => $valor_min_entrega_gratis
    ]);
    echo "</pre>";


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
                    // Converte a string de cartões em array
                    $car_debito = isset($formas_recebimento_str) && in_array('Cartão de Débito', $formas_recebimento) 
                        ? explode(',', $formas_recebimento_str) 
                        : [];
                    
                    // Exibe os checkboxes para os cartões
                    foreach ($lista_cartoes as $cartao) : 
                ?>
                <label>
                    <input type="checkbox" name="cartoes_debito[]" value="<?php echo htmlspecialchars($cartao['nome']); ?>"
                    <?php echo (in_array($cartao['nome'], $car_debito)) ? 'checked' : ''; ?>>
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
                // Converte a string em array
                $car_credito = isset($formas_recebimento['Cartão Crédito']) ? explode(',', $formas_recebimento['Cartão Crédito']) : [];
                
                // Exibe os checkboxes para os cartões
                foreach ($lista_cartoes as $cartao) : ?>
                    <label>
                        <input type="checkbox" name="cartoes_credito[]" value="<?php echo htmlspecialchars($cartao['nome']); ?>"
                        <?php echo (in_array($cartao['nome'], $car_credito)) ? 'checked' : ''; ?>>
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
            <input type="text" step="0.01" name="valor_minimo_pedido" value="<?php echo htmlspecialchars($valor_minimo_pedido); ?>" required>
        </fieldset>

        <!-- Valor Mínimo de Pedido -->
        <fieldset>
            <legend>Valor Mínimo de compra para ter entrega Gratís</legend>
            <label>Informe o valor mínimo:</label>
            <input type="text" step="0.01" name="valor_min_entrega_gratis" value="<?php echo htmlspecialchars($valor_min_entrega_gratis); ?>" required>
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
    </script>
</html>
