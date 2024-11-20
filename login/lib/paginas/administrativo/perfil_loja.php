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
        $sql_query = $mysqli->query(query: "SELECT * FROM meus_clientes WHERE id = '$id'") or die($mysqli->error);
        $admin = $sql_query->fetch_assoc();

        // Consulta para buscar o parceiro
        $sql_query = $mysqli->query("SELECT * FROM meus_clientes WHERE id = '$id'") or die($mysqli->error);

        // Verifica se o ID existe
        if ($sql_query->num_rows > 0) {
            // ID encontrado, continue com a lógica
            //$parceiro = $sql_query->fetch_assoc();
            
            $sql_query = $mysqli->query("
                SELECT * 
                FROM config_admin 
                WHERE nomeFantasia != '' 
                ORDER BY data_alteracao DESC 
                LIMIT 1
            ") or die($mysqli->error);
            
            $dados = $sql_query->fetch_assoc();
        

            //$sql_query = $mysqli->query("SELECT * FROM meus_parceiros WHERE id = '$id'") or die($mysqli->$error);
            //$dados = $sql_query->fetch_assoc();
            $minhaLogo = $dados['logo'];
            //echo ('oii').$minhaLogo;
            if ($minhaLogo !=''){
                // Se existe e não está vazio, atribui o valor à variável logo
                $logo = 'arquivos/'.$dados['logo'];
                //echo ('oii').$logo;
            } else {
                // Se não existe ou está vazio, define um valor padrão
                $logo = '../arquivos_fixos/icone_loja.png';
            }
        } else {
            //echo ('oi1');
            session_unset();
            session_destroy(); 
            header("Location: ../../../../index.php");
            exit();
        }


    } else {
        //echo ('oi2');
        session_unset();
        session_destroy(); 
        header("Location: ../../../../index.php");
        exit();
    }

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

</head>
<body>
    
    <form id="cadastroEmpresa" action="atualizar.php" method="POST" enctype="multipart/form-data">

        <h2>Dados da Loja.</h2>

        <div style="text-align: center;">
            <input type="hidden" id="id_sessao" name="img_anterior" value="<?php echo $id; ?>">
            
            <!-- Armazena o caminho da imagem anterior -->
            <input type="hidden" id="img_anterior" name="img_anterior" value="<?php echo $logo; ?>">
            
            <!-- Exibe a logo atual com um ID para ser acessado pelo JavaScript -->
            <img id="logoPreview" class="file-preview" src="<?php echo $logo; ?>" alt="Pré-visualização da Logo" style="width: 100px; length: 100px;" required><br>
            
            <!-- Input de arquivo com restrição para tipos de arquivo permitidos -->
            <input type="file" id="logoInput" name="logoInput"  accept=".jpg, .jpeg, .png, .gif">
        </div>

        <span id="msgAlerta"></span><br>

        <input type="hidden" id="primeiro_nome" name="primeiro_nome" required value="<?php echo $dados['primeiro_nome']?>">
        
        <label for="razao">Razão Social:</label>
        <input type="text" id="razao" name="razao" required value="<?php echo $dados['razao']?>">

        <label for="nomeFantasia">Nome Fantasia:</label>
        <input type="text" id="nomeFantasia" name="nomeFantasia" required value="<?php echo $dados['nomeFantasia']?>">

        <label for="cnpj">CNPJ:</label>
        <input type="text" id="cnpj" name="cnpj" required value="<?php echo $dados['cnpj']?>" oninput="formatCNPJ(this)" onblur="verificaCNPJ()">

        <label for="inscricaoEstadual">Inscrição Estadual:</label>
        <input type="text" id="inscricaoEstadual" name="inscricaoEstadual" required value="<?php echo $dados['inscricaoEstadual'];?>" oninput="this.value = this.value.replace(/\D/g, '')">

        <label for="telefoneComercial">Telefone Comercial:(WhatsApp)</label>
        <input type="text" id="telefoneComercial" name="telefoneComercial" required value="<?php echo $dados['telefoneComercial']?>" placeholder="(00) 00000-0000" oninput="formatarCelular(this)" onblur="verificaCelular1()">

        <label for="email">E-mail:</label>
        <input required name="email" id="email" type="email" required value="<?php echo $dados['email_suporte']?>" >

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

        <div class="action-buttons">
            <a href="admin_home.php" class="link-voltar"><b>Voltar</b></a>
            <button type="submit" id="salvar">Salvar</button>
        </div>

    </form>

    <script src="perfil_loja.js"></script>

</body>
</html>
