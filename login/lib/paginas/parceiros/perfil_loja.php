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
        if(isset($parceiro['logo'])) {
            $logo = $parceiro['logo'];
            if($logo === ''){
                $logo = '../arquivos_fixos/icone_loja.jpg';
            } else {
                $logo = '../arquivos_fixos/'. $logo;
            }
        }
    } else {
        session_unset();
        session_destroy(); 
        header("Location: ../../../../index.php");
        exit();
    }

    $id = $_SESSION['id'];
    $sql_query = $mysqli->query("SELECT * FROM meus_parceiros WHERE id = '$id'") or die($mysqli->$error);
    $dados = $sql_query->fetch_assoc();

    if(isset($dados['logo'])) {
        $logo = $dados['logo'];
        if($logo == ''){
            $logo = 'arquivos/'.$logo;
        }
    }
    if(!isset($logo['foto'])) {
        $logo = '../arquivos_fixos/icone_loja.jpg';
    }


    // Consulta para buscar produtos do catálogo
    $produtos_catalogo = $mysqli->query(query: "SELECT * FROM produtos WHERE id_loja = '$id'") or die($mysqli->error);

    // Verifica se existem promoções, mais vendidos e frete grátis
    $promocoes = $mysqli->query(query: "SELECT * FROM produtos WHERE promocao = 1 AND id_loja = '$id'");
    $mais_vendidos = $mysqli->query(query: "SELECT * FROM produtos WHERE mais_vendidos = 1 AND id_loja = '$id'");
    $frete_gratis = $mysqli->query(query: "SELECT * FROM produtos WHERE frete_gratis = 1 AND id_loja = '$id'");
?>      
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- <script src="localizador.js" defer></script> //Inclui o arquivo JS -->
    <title>perfil da Loja</title>
    <link rel="stylesheet" href="perfil_loja.css">
</head>
<body>
    
    <form id="cadastroEmpresa" action="processa_cadastro.php" method="POST" enctype="multipart/form-data">

        <h2>Dados da Loja.</h2>

        <img src="<?php echo $logo; ?>" alt="" style="max-width: 200px;">

        <span id="msgAlerta"></span><br>

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

        <!-- Div para visualização da imagem ou do nome do arquivo -->
        <div id="filePreview" class="file-preview"></div>
        <div class="file-upload-container">
            <label for="arquivoEmpresa">Comprovante de Inscrição e de Situação Cadastral (PDF ou PNG):</label>
            <img src="arquivos/<?php echo $dados['anexo_comprovante'];?>" alt="" style="max-width: 400px;">
            <input type="file" id="arquivoEmpresa" name="arquivoEmpresa" accept=".pdf, .png" required value="<?php echo $dados['anexo_comprovante']?>">
        </div>

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

        <a href="termos.php" target="_blank"><b>Termos</b></a>.

        <div class="action-buttons">
            <a href="parceiro_home.php" class="link-voltar"><b>Voltar</b></a>
            <button type="submit" id="cadastrar" disabled>Cadastrar</button>
        </div>

    </form>

    <script src="perfil_loja.js"></script>
    
</body>
</html>
