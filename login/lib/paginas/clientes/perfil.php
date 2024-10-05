<?php
    //codigo da sessão
    include('../../conexao.php');

    if(!isset($_SESSION))
        session_start();
    
    if(!isset($_SESSION['usuario'])){
        header("Location: ../../../../index.php");
    }
    if(isset($_SESSION['email'])){

        $email = $_SESSION['email'];
        $senha = password_hash($_SESSION['senha'], PASSWORD_DEFAULT);
        $mysqli->query("INSERT INTO senha (email, senha) VALUES('$email','$senha')");
    
    }
    
    $id = $_SESSION['usuario'];
    $sql_query = $mysqli->query("SELECT * FROM socios WHERE id = '$id'") or die($mysqli->$error);
    $usuario = $sql_query->fetch_assoc();

    if(isset($usuario['foto'])) {
        $foto = $usuario['foto'];
        if($foto == ''){
            $foto = '../arquivos_fixos/9734564-default-avatar-profile-icon-of-social-media-user-vetor.jpg';
        }
    }
    if(!isset($usuario['foto'])) {
        $foto = '../arquivos_fixos/9734564-default-avatar-profile-icon-of-social-media-user-vetor.jpg';
    }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body h2{
            text-align: center;
        }
        body form{
            text-align: center;
            border: 1px solid black;
            width: 95%;
            /*position: absolute;*/
            /*top: 50%;
            left: 50%;
            /*transform: translate(-50%, -50%);*/
            padding: 15px;
        }
        img{
            width: 50%;
            /*text-align: center;*/
            border-radius: 10px;
        }
        form label{
            margin: 10px;
            /*padding: 50px;*/
        }
        /*form label,*/
        form input {
            margin-bottom: 10px;
            max-width: 50%;
        }
        form .dados label {
            display: inline-block;
            width: 100%; /* Faz a label ocupar 100% da largura do contêiner pai */
            max-width: 20%; /* Define a largura máxima desejada */
            box-sizing: border-box; /* Inclui a largura da borda e o preenchimento na largura total */
        }
        form .dados input {
            /*width: 50%;*/
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        form .dados select {
            width: 25%;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        form .endereco label {
            display: inline-block;
            width: 100%; /* Faz a label ocupar 100% da largura do contêiner pai */
            max-width: 20%; /* Define a largura máxima desejada */
            box-sizing: border-box; /* Inclui a largura da borda e o preenchimento na largura total */
        }
        form .endereco input {
            width: 50%;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        form .endereco select {
            width: 25%;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        form .contatos label {
            display: inline-block;
            width: 100%; /* Faz a label ocupar 100% da largura do contêiner pai */
            max-width: 20%; /* Define a largura máxima desejada */
            box-sizing: border-box; /* Inclui a largura da borda e o preenchimento na largura total */
        }
        form .contatos input {
            width: 50%;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input{
            margin: 5px;
        }
        textarea {
            width: 95%; /* Define a largura do textarea */
            height: 150px; /* Define a altura do textarea */
            padding: 10px; /* Adiciona preenchimento interno */
            font-size: 16px; /* Define o tamanho da fonte */
            border: 1px solid #ccc; /* Adiciona uma borda de 1 pixel sólida com cor cinza */
            border-radius: 5px; /* Adiciona bordas arredondadas */
            background-color: #f8f8f8; /* Define a cor de fundo */
            color: #333; /* Define a cor do texto */
        }

        textarea:focus {
            outline: none; /* Remove a borda de foco padrão */
            border-color: #007bff; /* Define a cor da borda quando em foco */
        }
        /* Estilize o container do rádio */
        input[type="radio"]{
            
            margin-bottom: 0px;
            margin: 0;
        }
        label .radio{
            margin-left: 0px;
        }
        a{
            margin-left: 100px; 
            margin-right: 40px;
        }
    </style>
    <title>Meu Perfil</title>
</head>
<body>
    <form id="iform" action="alterar_dados_perfil.php" method="POST" enctype="multipart/form-data" autocomplete="on" onsubmit="return validateForm()">
        <p>
            <img id="ifoto" style="max-width: 200px;" src= "<?php echo $foto; ?>" name="foto_antiga" alt=""><br>
            <img id="ifotoNova" style="max-width: 200px;" alt=""><br>
            <label for="imageInput">Alterar Foto </label><input type="file" id="imageInput" name="imageInput" accept=".png, .jpg, .jpeg" onchange="imgPerfil(event)">
            <input type="hidden" name="end_foto" value= "<?php echo $foto; ?>">
        </p>  
        
        <input id="" value="<?php echo $usuario['id']; ?>" name="id" type="hidden">
        <fieldset class="dados">
            <legend>Meus Dados</legend>
            <p>
                <label for="inome_completo" >Nome Completo: </label>
                <input required id="inome_completo" value="<?php echo $usuario['nome_completo']; ?>" name="nome_completo" type="text"><br>
            </p>
            <p>
                <label for="iapelido" >Apelido: </label>
                <input required id="iapelido" value="<?php echo $usuario['apelido']; ?>" name="apelido" type="text"><br>
            </p>
            <p>
                <label for="icpf" >CPF: </label>
                <input required id="icpf" value="<?php echo $usuario['cpf']; ?>" name="cpf" type="text" oninput="formatCPF(this)" onblur="verificaCpf()"><br>
            </p>
            <p>
                <label for="irg" >RG: </label>
                <input required id="irg" value="<?php echo $usuario['rg']; ?>" name="rg" type="text" oninput="formatRG(this)" onblur="verificaRG()"><br>
            </p>
            <p>
                <label for="inascimento" >Data de Nascimento: </label>
                <?php
                    // Suponha que $usuario seja um array contendo os dados do banco de dados, incluindo o campo "data_nascimento"
                    $dataNascimento = $usuario['nascimento'];

                    // Formate a data para o formato brasileiro (dd/mm/yyyy)
                    $dataNascimentoFormatada = date('d/m/Y', strtotime($dataNascimento));
                ?>
                <input required id="inascimento" value="<?php echo $dataNascimentoFormatada; ?>" name="nascimento" type="text"  oninput="formatarData(this)" onblur="verificaData()"><br>
            </p>
            <p>
                <label id="" for="iuf">Estado Natal: </label>
                <select required name="uf" id="iuf">
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

                        $ufSelecionada = $usuario['uf'];

                        echo '<option value="' . $ufSelecionada . '">' . $estados[$ufSelecionada] . '</option>';

                        foreach ($estados as $uf => $estado) {
                            if ($uf !== $ufSelecionada) {
                                echo '<option value="' . $uf . '">' . $estado . '</option>';
                            }
                            }           
                ?>

                <!-- Adicione mais opções para outros estados aqui -->
                </select>
                
            </p>
            <p>
                <label for="icid_natal" >Cidade Natal: </label>
                <input required id="icid_natal" value="<?php echo $usuario['cid_natal']; ?>" name="cidnatal" type="text"><br>
            </p>
            <p>
                <label id="" for="imae">Nome da Mãe: </label>
                <input required id="imae" value="<?php echo $usuario['mae']; ?>" name="mae" type="text"><br>
            </p>
            <p>
                <label id="" for="ipai">Nome do Pai: </label>
                <input id="ipai" value="<?php echo $usuario['pai']; ?>" name="pai" type="text"><br>
            </p>
        </fieldset>
        <fieldset class="sexo">
            <legend>Sexo</legend>
            <p>
                <?php
                    // Suponha que $usuario seja um array contendo os dados do banco de dados, incluindo o campo "sexo"
                    $sexoMasculino = ($usuario['sexo'] == 'MASCULINO') ? 'checked' : '';
                    $sexoFeminino = ($usuario['sexo'] == 'FEMININO') ? 'checked' : '';
                    $sexoOutro = ($usuario['sexo'] == 'OUTROS') ? 'checked' : '';
                ?>
                <input type="radio" name="sexo" id="imasc" value="MASCULINO" <?php echo $sexoMasculino; ?>><label for="imasc">Masculino</label> 
                <input type="radio" name="sexo" id="ifemi" value="FEMININO" <?php echo $sexoFeminino; ?>><label for="ifemi">Feminino</label> 
                <input type="radio" name="sexo" id="iout" value="OUTROS" <?php echo $sexoOutro; ?>><label for="iout">Outros</label>

            </p>
        </fieldset>
        <fieldset class="endereco">
            <legend>Endereço Atual</legend>
            <p> 
                <label for="iuf_atual">Estado Atual: </label>
                <select required name="uf_atual" id="iuf_atual" value="">
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

                        $ufSelecionada = $usuario['uf'];

                        echo '<option value="' . $ufSelecionada . '">' . $estados[$ufSelecionada] . '</option>';

                        foreach ($estados as $uf => $estado) {
                            if ($uf !== $ufSelecionada) {
                                echo '<option value="' . $uf . '">' . $estado . '</option>';
                            }
                        }           
                    ?>

                <!-- Adicione mais opções para outros estados aqui -->
                </select>
            </p>
            <p>
                <label id="" for="icep">CEP: </label><br>
                <input required value="<?php echo $usuario['cep']; ?>" name="cep" id="icep" type="text" maxlength="9" oninput="formatarCEP(this)" onblur="fetchCityByCEP()"><br>
            </p>
            <p>
                <label id="" for="icid_atual">Cidade Atual: </label><br>
                <input required value="<?php echo $usuario['cid_atual']; ?>" name="cid_atual" id="icid_atual" type="text"><br>
            </p>
            <p>
                <label id="" for="iendereco">Logradouro: AV/RUA </label><br>
                <input required value="<?php echo $usuario['endereco']; ?>" name="endereco" id="iendereco" type="text"><br>
            </p>
            <p>
                <label id="" for="inum">N°: </label><br>
                <input required value="<?php echo $usuario['numero']; ?>" name="numero" id="inum" type="text"><br>
            </p>
            <p>
                <label id="" for="ibairro">Bairro: </label><br>
                <input required value="<?php echo $usuario['bairro']; ?>" name="bairro" id="ibairro" type="text"><br>
            </p>
        </fieldset>
        <fieldset class="contatos">
            <legend>Contatos</legend>
            <p>
                <label id="" for="icelular1">Celular 1: </label><br>
                <input required value="<?php echo $usuario['celular1']; ?>" name="celular1" id="icelular1" type="text" placeholder="(00) 00000-0000" size="" oninput="formatarCelular1(this)" onblur="verificaCelular1()"><br>
            </p>
            <p>
                <label id="" for="icelular2">Celular 2: Opcional </label><br>
                <input value="<?php echo $usuario['celular2']; ?>" name="celular2" id="icelular2" type="text" placeholder="(00) 00000-0000" size="" oninput="formatarCelular2(this)" onblur="verificaCelular2()"><br>
            </p>
            <p>
                <label id="" for="iemail">E-mail:</label><br>
                <input required value="<?php echo $usuario['email']; ?>" name="email" id="iemail" type="email"><br>
            </p>
        </fieldset>
        <p>
            <span id="imgAlerta"></span><br>
            <span id="imgAlerta2" type="hidden"></span><br>
            <a href="inicio.php" style="margin-left: 10px; margin-right: 10px;">Voltar</a><a href="../../redefinir_senha.php" style="margin-left: 10px; margin-right: 10px;">Redefinir Senha</a>
            <button id="" type="submit" style="margin-left: 10px;">Salvar</button>
        </p>
        <script src="perfil_verifica_dados.js"></script>
    </form>
</body>
</html>

