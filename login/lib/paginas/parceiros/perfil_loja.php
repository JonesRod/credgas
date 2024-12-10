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
        $sql_query = $mysqli->query(query: "SELECT * FROM meus_parceiros WHERE id = '$id'") or die($mysqli->error);
        $parceiro = $sql_query->fetch_assoc();

        // Verifica e ajusta a logo
        /*if(isset($parceiro['logo'])) {
            $logo = $parceiro['logo'];
            if($logo === ''){
                $logo = '../arquivos_fixos/icone_loja.jpg';
            } else {
                $logo = 'arquivos/'. $logo;
            }
        }*/
    } else {
        session_unset();
        session_destroy(); 
        header("Location: ../../../../index.php");
        exit();
    }

    $id = $_SESSION['id'];
    $sql_query = $mysqli->query("SELECT * FROM meus_parceiros WHERE id = '$id'") or die($mysqli->$error);
    $dados = $sql_query->fetch_assoc();
    $minhaLogo = $dados['logo'];

    if ($minhaLogo !=''){
        // Se existe e não está vazio, atribui o valor à variável logo
        $logo = $dados['logo'];
        //echo ('oii').$logo;
    } else {
        // Se não existe ou está vazio, define um valor padrão
        $logo = '../arquivos_fixos/icone_loja.jpg';
    }

    // Verifica se o valor 'aberto_fechado' está presente nos dados e atribui 'Aberto' ou 'Fechado'
    $statusLoja =  $dados['aberto_fechado_manual'];
    $statusChecked = $statusLoja === 'Aberto' ? 'checked' : ''; // Define 'checked' se a loja estiver aberta

    // Verifica se o valor 'aberto_fechado' está presente nos dados e atribui 'Aberto' ou 'Fechado'
    $statusLojaaut =  $dados['aberto_fechado_aut'];
    $statusCheckedaut = $statusLojaaut === 'Ativado' ? 'checked' : ''; // Define 'checked' se a loja estiver aberta

    //echo ('').$statusCheckedaut;
    // Exibe o valor de $dados['aberto_fechado'] para depuração
    //var_dump($dados['aberto_fechado']); // ou echo $dados['aberto_fechado'];

    // Consulta para buscar produtos do catálogo
    $produtos_catalogo = $mysqli->query(query: "SELECT * FROM produtos WHERE id_parceiro = '$id'") or die($mysqli->error);

    // Verifica se existem promoções, mais vendidos e frete grátis
    $promocoes = $mysqli->query(query: "SELECT * FROM produtos WHERE promocao = 1 AND id_parceiro = '$id'");
    $mais_vendidos = $mysqli->query(query: "SELECT * FROM produtos WHERE mais_vendidos = 1 AND id_parceiro = '$id'");
    $frete_gratis = $mysqli->query(query: "SELECT * FROM produtos WHERE frete_gratis = 1 AND id_parceiro = '$id'");
?>      
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="localizador.js" defer></script>
    <title>perfil da Loja</title>
    <link rel="stylesheet" href="perfil_loja.css">
    <style>
        .switch {
            position: relative;
            /*display: inline-block;*/
            width: 60px;
            height: 30px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 30px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #4caf50;
        }

        input:checked + .slider:before {
            transform: translateX(30px);
        }
        #statusLojaTextoDisplay {
            display: inline-block;
            margin-top: 10px; /* Ajusta a distância do texto */
            margin-left: 10px; /* Ajusta a posição horizontal, se necessário */
            vertical-align: middle; /* Mantém alinhamento com o botão */
            font-size: 16px; /* Ajuste do tamanho da fonte */
            color: #333; /* Cor do texto */
        }

    </style>
</head>
<body>
    
    <form id="cadastroEmpresa" action="processa_cadastro.php" method="POST" enctype="multipart/form-data">

        <h2>Dados da Loja.</h2>

        <div style="text-align: center;">
            <input type="hidden" id="id_sessao" name="img_anterior" value="<?php echo $id; ?>">
            
            <!-- Armazena o caminho da imagem anterior -->
            <input type="hidden" id="img_anterior" name="img_anterior" value="arquivos/<?php echo $logo; ?>">
            
            <!-- Exibe a logo atual com um ID para ser acessado pelo JavaScript -->
            <img id="logoPreview" class="file-preview" src="arquivos/<?php echo $logo; ?>" alt="Pré-visualização da Logo" style="max-width: 200px;" required><br>
            
            <!-- Input de arquivo com restrição para tipos de arquivo permitidos -->
            <input type="file" id="logoInput" name="logoInput"  accept=".jpg, .jpeg, .png, .gif">
        </div>

        <span id="msgAlerta"></span><br>

        <div style="margin-bottom: 20px; text-align: center; display: flex; flex-direction: column; align-items: center;">
            <!-- Controle Manual -->
            <div style="margin-bottom: 20px; display: flex; align-items: center; justify-content: center;">
                <label for="statusLojaManual" style="margin-right: 10px;">Status da Loja (Manual):</label>
                <label class="switch" style="margin-right: 10px;">
                    <input type="checkbox" id="aberto_fechado_manual" name="aberto_fechado_manual" <?php echo $statusChecked; ?> onchange="atualizarStatusManual();">
                    <span class="slider"></span>
                </label>
                <span id="statusLojaTextoDisplayManual">Fechado</span>
                <input type="hidden" id="statusLojaTextoManual" name="statusLojaTextoManual" value="Fechado">
            </div>

            <!-- Controle Automático -->
            <div style="margin-bottom: 20px; display: flex; align-items: center; justify-content: center;">
                <label for="statusLojaAuto" style="margin-right: 10px;">Ativar Modo Automático:</label>
                <label class="switch" style="margin-right: 10px;">
                    <input type="checkbox" id="modoAutomatico" name="modoAutomatico" <?php echo $statusCheckedaut; ?> onchange="ativarModoAutomatico();">
                    <span class="slider"></span>
                </label>
                <span id="statusLojaAutoTextoDisplay">Desativado</span>
                <input type="hidden" id="statusLojaAutoTexto" name="statusLojaAutoTexto" value="Desativado">
            </div>
        </div>


        <label for="razao">Razão Social:</label>
        <input type="text" id="razao" name="razao" required value="<?php echo $dados['razao']?>">

        <label for="nomeFantasia">Nome Fantasia:</label>
        <input type="text" id="nomeFantasia" name="nomeFantasia" required value="<?php echo $dados['nomeFantasia']?>">

        <label for="cnpj">CNPJ:</label>
        <input type="text" id="cnpj" name="cnpj" required value="<?php echo $dados['cnpj']?>" oninput="formatCNPJ(this)" onblur="verificaCNPJ()">

        <label for="inscricaoEstadual">Inscrição Estadual:</label>
        <input type="text" id="inscricaoEstadual" name="inscricaoEstadual" required value="<?php echo $dados['inscricaoEstadual'];?>" oninput="this.value = this.value.replace(/\D/g, '')">

        <label for="categoria">Categoria:</label>
        <input type="text" id="categoria" name="categoria" required value="<?php echo $dados['categoria']?>"> 

        <label for="telefoneComercial">Telefone Comercial:(WhatsApp)</label>
        <input type="text" id="telefoneComercial" name="telefoneComercial" required value="<?php echo $dados['telefoneComercial']?>" placeholder="(00) 00000-0000" oninput="formatarCelular(this)" onblur="verificaCelular1()">

        <label for="telefoneResponsavel">Telefone do Responsável:(WhatsApp)</label>
        <input type="text" id="telefoneResponsavel" name="telefoneResponsavel" required value="<?php echo $dados['telefoneResponsavel']?>" placeholder="(00) 00000-0000" oninput="formatarCelular(this)" onblur="verificaCelular2()">
        
        <label for="email">E-mail:</label>
        <input required name="email" id="email" type="email" required value="<?php echo $dados['email']?>" >

        <p id="status-localizacao">Localização: Ativa</p>

        <label for="cep">CEP:</label>
        <input required name="cep" id="cep" type="text" value="<?php echo $dados['cep']?>" maxlength="9" oninput="formatarCEP(this)" onblur="buscarCidadeUF()">

        <label for="uf">Estado: </label>
        <select required name="uf" id="uf">
            <?php
                $estados = array(
                'AC' => 'Acre',
                'AL' => 'Alagoas',
                'AP' => 'Amapá',
                'AM' => 'Amazonas',
                'BA' => 'Bahia',
                'CE' => 'Ceará' ,
                'DF' => 'Distrito Federal',
                'ES' => 'Espírito Santo',
                'GO' => 'Goiás',
                'MA' => 'Maranhão',
                'MS' => 'Mato Grosso do Sul',
                'MT' => 'Mato Grosso',
                'MG' => 'Minas Gerais',
                'PA' => 'Pará',
                'PB' => 'Paraíba',
                'PR' => 'Paraná',
                'PE' => 'Pernambuco',
                'PI' => 'Piauí',
                'RJ' => 'Rio de Janeiro',
                'RN' => 'Rio Grande do Norte',
                'RS' => 'Rio Grande do Sul',
                'RO' => 'Rondônia',
                'RR' => 'Roraima',
                'SC' => 'Santa Catarina',
                'SP' => 'São Paulo',
                'SE' => 'Sergipe',
                'TO' => 'Tocantins'
                );

                $ufSelecionada = $dados['estado'];

                echo '<option value="' . $ufSelecionada . '">' . $estados[$ufSelecionada] . '</option>';

                foreach ($estados as $uf => $estado) {
                    if ($uf !== $ufSelecionada) {
                        echo '<option value="' . $uf . '">' . $estado . '</option>';
                    }
                    }           
        ?>

        <!-- Adicione mais opções para outros estados aqui -->
        </select>

        <label for="cidade">Cidade:</label>
        <input type="text" id="cidade" name="cidade" required value="<?php echo $dados['cidade']?>">

        <label for="rua">RUA/AV:</label>
        <input type="text" id="rua" name="rua" required value="<?php echo $dados['endereco']?>" >

        <label for="numero">Numero:</label>
        <input type="text" id="numero" name="numero" required value="<?php echo $dados['numero']?>">

        <label for="bairro">Bairro:</label>
        <input type="text" id="bairro" name="bairro" required value="<?php echo $dados['bairro']?>">

        // Leia os <a href="termos.php" target="_blank"><b>Termos</b></a>.

        <div class="action-buttons">
            <a href="parceiro_home.php" class="link-voltar"><b>Voltar</b></a>
            <button type="submit" id="cadastrar" <?php echo ($dados['analize_inscricao'] === 'aprovado') ? '' : 'disabled'; ?>>Salvar</button>
        </div>

    </form>

    <script src="perfil_loja.js"></script>
    <script>
        function atualizarStatusManual() {
            var checkbox = document.getElementById('aberto_fechado_manual');
            var statusDisplay = document.getElementById('statusLojaTextoDisplayManual');
            var hiddenInput = document.getElementById('statusLojaTextoManual');

            if (checkbox.checked) {
                statusDisplay.textContent = 'Aberto';
                hiddenInput.value = 'Aberto';
            } else {
                statusDisplay.textContent = 'Fechado';
                hiddenInput.value = 'Fechado';
            }
        }

        function ativarModoAutomatico() {
            var checkbox = document.getElementById('modoAutomatico');
            var statusDisplay = document.getElementById('statusLojaAutoTextoDisplay');
            var hiddenInput = document.getElementById('statusLojaAutoTexto');

            if (checkbox.checked) {
                statusDisplay.textContent = 'Ativado';
                hiddenInput.value = 'Ativado';
                
                // Desativar o modo manual quando o automático for ativado
                document.getElementById('aberto_fechado_manual').disabled = true;
            } else {
                statusDisplay.textContent = 'Desativado';
                hiddenInput.value = 'Desativado';

                // Reativar o modo manual quando o automático for desativado
                document.getElementById('aberto_fechado_manual').disabled = false;
            }
        }

        // Ajusta os estados iniciais ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            atualizarStatusManual();
            ativarModoAutomatico();
        });

</script>

</body>
</html>
