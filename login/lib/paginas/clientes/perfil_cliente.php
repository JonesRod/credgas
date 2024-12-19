<?php
    include('../../conexao.php');

    // Inicia a sessão
    if (!isset($_SESSION)) {
        session_start(); 
    }

    // Verifica se o usuário está logado
    if (isset($_SESSION['id']) || isset($_GET['id'])) {
        $id = $_SESSION['id'] ?? $_GET['id'];

        // Consulta para buscar o parceiro
        $sql_query = $mysqli->query(query: "SELECT * FROM meus_clientes WHERE id = '$id'") or die($mysqli->error);
        $dados = $sql_query->fetch_assoc();

    } else {
        session_unset();
        session_destroy(); 
        header("Location: ../../../../index.php");
        exit();
    }

    $imagem = $dados['imagem'];

    if ($imagem !=''){
        // Se existe e não está vazio, atribui o valor à variável logo
        $imagem = 'arquivos/'.$dados['imagem'];
        //echo ('oii').$logo;
    } else {
        // Se não existe ou está vazio, define um valor padrão
        $imagem = '../arquivos_fixos/avatar_icone.jpg';
    }

?>      
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Dados</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
            line-height: 1.6;
        }

        form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #444;
        }

        label {
            display: block;
            margin: 15px 0 5px;
            font-weight: bold;
        }

        input, select, button {
            width: 95%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        input[readonly] {
            background-color: #e9ecef;
            cursor: not-allowed;
        }

        .file-preview {
            display: block;
            margin: 0 auto 10px;
            border: 1px solid #ccc;
            border-radius: 50%;
            max-width: 150px;
            max-height: 150px;
            object-fit: cover;
        }

        .contatos legend {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .action-buttons {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        .action-buttons a {
            width: calc(50% - 5px);
            padding: 10px;
            margin-bottom: 16px;
            font-size: 14px;
            text-align: center;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
        }

        .action-buttons button {
            width: calc(50% - 5px);
            padding: 10px;
            font-size: 14px;
            text-align: center;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
        }

        .action-buttons button:hover {
            background-color: #0056b3;
        }

        .link-voltar:hover {
            background-color: #0056b3;
        }
        .contatos{
            border-radius: 5px;
        }
        .contatos button{
            width: 99%;
            color: #fff;
            background-color: #007bff;
        }

        .contatos button:hover{
            background-color: #0056b3;
        }
        .alterar button{
            color: #fff;
            background-color: #007bff;
        }
        .alterar button:hover{
            background-color: #0056b3;
        }
        @media (max-width: 768px) {
            form {
                padding: 15px;
            }

            h2 {
                font-size: 18px;
            }

            input, select, button {
                font-size: 14px;
            }

            .file-preview {
                max-width: 120px;
                max-height: 120px;
            }

            .action-buttons button {
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            form {
                padding: 10px;
            }

            input, select, button {
                font-size: 12px;
            }

            .file-preview {
                max-width: 100px;
                max-height: 100px;
            }

           .action-buttons button {
                font-size: 12px;
                padding: 8px;
            }
        }
    </style>
</head>
<body>

    <form id="meusDados" action="processa_alteracao.php" method="POST" enctype="multipart/form-data">

        <h2>Meus Dados</h2>

        <div style="text-align: center;">
            <input type="hidden" id="id" name="img_anterior" value="<?php echo $id; ?>">
            <input type="hidden" id="img_anterior" name="img_anterior" value="<?php echo $imagem; ?>">
            <img id="logoPreview" class="file-preview" src="<?php echo $imagem; ?>" alt="Pré-visualização imagem"><br>
            <input type="file" id="logoInput" name="logoInput" accept=".jpg, .jpeg, .png, .gif">
        </div>

        <label for="nome">Nome Completo:</label>
        <input type="text" id="nome" name="nome" readonly value="<?php echo $dados['nome_completo']?>">

        <label for="cpf">CPF:</label>
        <input type="text" id="cpf" name="cpf" readonly value="<?php echo $dados['cpf']?>">

        <label for="data_nasc">Data de Nascimento:</label>
        <input type="text" id="data_nasc" name="data_nasc" readonly value="<?php
            $dataOriginal = $dados['nascimento'];
            $dataFormatada = DateTime::createFromFormat('Y-m-d', $dataOriginal)->format('d/m/Y');
            echo $dataFormatada;
        ?>">

        <fieldset class="contatos">
            <legend>Contatos</legend>
                <label for="telefone1">Telefone (WhatsApp):</label>
                <input type="text" id="telefone1" name="telefone1" readonly value="<?php echo $dados['celular1']?>">

                <label for="telefone2">Telefone Adicional (Opcional):</label>
                <input type="text" id="telefone2" name="telefone2" readonly value="<?php echo $dados['celular2']?>">

                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" readonly value="<?php echo $dados['email']?>">
                <button>Alterar</button>
        </fieldset>

        <label for="cep">CEP:</label>
        <input type="text" id="cep" name="cep" value="<?php echo $dados['cep']?>" maxlength="9" oninput="formatarCEP(this)" onblur="buscarCidadeUF()">

        <label for="uf">Estado:</label>
        <select id="uf" name="uf">
            <?php
                $ufSelecionada = $dados['uf'];
                $estados = [
                    'AC' => 'Acre', 'AL' => 'Alagoas', 'AP' => 'Amapá', 'AM' => 'Amazonas',
                    'BA' => 'Bahia', 'CE' => 'Ceará', 'DF' => 'Distrito Federal', 'ES' => 'Espírito Santo',
                    'GO' => 'Goiás', 'MA' => 'Maranhão', 'MS' => 'Mato Grosso do Sul', 'MT' => 'Mato Grosso',
                    'MG' => 'Minas Gerais', 'PA' => 'Pará', 'PB' => 'Paraíba', 'PR' => 'Paraná',
                    'PE' => 'Pernambuco', 'PI' => 'Piauí', 'RJ' => 'Rio de Janeiro', 'RN' => 'Rio Grande do Norte',
                    'RS' => 'Rio Grande do Sul', 'RO' => 'Rondônia', 'RR' => 'Roraima', 'SC' => 'Santa Catarina',
                    'SP' => 'São Paulo', 'SE' => 'Sergipe', 'TO' => 'Tocantins'
                ];
                echo "<option value='$ufSelecionada'>{$estados[$ufSelecionada]}</option>";
                foreach ($estados as $uf => $estado) {
                    if ($uf !== $ufSelecionada) {
                        echo "<option value='$uf'>$estado</option>";
                    }
                }
            ?>
        </select>

        <label for="cidade">Cidade:</label>
        <input type="text" id="cidade" name="cidade" value="<?php echo $dados['cidade']?>">

        <label for="rua">RUA/AV:</label>
        <input type="text" id="rua" name="rua" value="<?php echo $dados['endereco']?>">

        <label for="numero">Número:</label>
        <input type="text" id="numero" name="numero" value="<?php echo $dados['numero']?>">

        <label for="bairro">Bairro:</label>
        <input type="text" id="bairro" name="bairro" value="<?php echo $dados['bairro']?>">

        <p>// Leia os <a href="termos_privacidade.php" target="_blank"><b>Termos de privacidade</b></a>.</p>
        <p>// Leia os <a href="termos_cliente_vista.php" target="_blank"><b>Termos de compras</b></a>.</p>

        <div class="action-buttons">
            <a href="cliente_home.php" class="link-voltar"><b>Voltar</b></a>
            <button type="submit" id="cadastrar"><b>Salvar</b></button>
        </div>

    </form>

    <!-- Modal para solicitar a senha -->
    <div id="senhaModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 1000; justify-content: center; align-items: center;">
        <div style="background: white; padding: 20px; border-radius: 8px; width: 300px; text-align: center;">
            <h3>Confirme sua senha</h3>
            <form id="verificarSenhaForm">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required placeholder="Digite sua senha" style="width: 90%; margin-bottom: 10px;">
                <div class="alterar">
                    <button type="submit">Confirmar</button>
                    <button type="button" id="cancelarModal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Referências aos elementos
        const alterarBtn = document.querySelector("fieldset.contatos button");
        const senhaModal = document.getElementById("senhaModal");
        const cancelarModal = document.getElementById("cancelarModal");
        const verificarSenhaForm = document.getElementById("verificarSenhaForm");

        // Abrir o modal ao clicar no botão Alterar
        alterarBtn.addEventListener("click", function (event) {
            event.preventDefault(); // Impede o envio do formulário
            senhaModal.style.display = "flex"; // Mostra o modal
        });

        // Fechar o modal ao clicar em Cancelar
        cancelarModal.addEventListener("click", function () {
            senhaModal.style.display = "none";
        });

        // Verificar a senha ao enviar o formulário
        verificarSenhaForm.addEventListener("submit", function (event) {
            event.preventDefault(); // Impede o envio normal do formulário

            const senha = document.getElementById("senha").value;

            // Faz uma requisição para verificar a senha no servidor
            fetch('verificar_senha.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `senha=${encodeURIComponent(senha)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.sucesso) {
                    // Redireciona para a página de alteração
                    window.location.href = 'alterar_contatos.php';
                } else {
                    alert('Senha incorreta!');
                }
            })
            .catch(error => console.error('Erro ao verificar senha:', error));
        });
    </script>

    <script>
        function formatarCEP(input) {
            let value = input.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
            if (value.length > 8) {
                value = value.substr(0, 8);
            }
            if (value.length > 5) {
                value = value.replace(/(\d{5})/, '$1-');
            }
            input.value = value;
            buscarCidadeUF();
            //console.log(11);
        }
        async function buscarCidadeUF() {
            const cep = document.getElementById('cep').value.replace(/\D/g, ''); // Remove caracteres não numéricos

            if (cep.length != 8) {
                //document.querySelector('#msgAlerta').textContent = "CEP inválido! Preencha o campo corretamente.";
                document.querySelector('#cidade').value = "";
                document.querySelector('#uf').value = "";
                document.getElementById('cep').focus();
                return;
            }

            try {
                const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const data = await response.json();

                if (data.erro) {
                    //document.querySelector('#msgAlerta').textContent = "CEP não encontrado.";
                    document.querySelector('#cidade').value = "";
                    document.querySelector('#uf').value = "---Escolha---";
                    return;
                }

                //document.querySelector('#msgAlerta').textContent = "";
                document.getElementById('cidade').value = data.localidade;
                document.getElementById('uf').value = data.uf;

            } catch (error) {
                //document.querySelector('#msgAlerta').textContent = "Erro ao buscar o CEP. Tente novamente mais tarde.";
                console.error('Erro:', error);
            }
        }
        document.getElementById('logoInput').addEventListener('change', function (event) {
            const file = event.target.files[0]; // Obtém o arquivo selecionado
            const preview = document.getElementById('logoPreview'); // Obtém o elemento da imagem

            if (file) {
                const reader = new FileReader();

                // Quando o arquivo é carregado, atualiza o src da imagem
                reader.onload = function (e) {
                    preview.src = e.target.result; // Define o conteúdo da imagem
                };

                reader.readAsDataURL(file); // Lê o arquivo como Data URL
            } else {
                preview.src = ''; // Limpa a imagem caso nenhum arquivo seja selecionado
            }
        });


    </script>
</body>
</html>

