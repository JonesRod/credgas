<?php
    include('../../../conexao.php');

    if(!isset($_SESSION)){
        session_start(); 

        if(isset($_SESSION['usuario'])){

            if (isset($_POST["tipoLogin"])) {
                // echo "1";
                $usuario = $_SESSION['usuario'];
                $valorSelecionado = $_POST["tipoLogin"];// Obter o valor do input radio
                $admin = $valorSelecionado;

                if($admin != 1){
                    $usuario = $_SESSION['usuario'];
                    $admin = $_SESSION['admin'];
                    //echo "1";
                    header("Location: ../../usuarios/usuario_home.php");      
                }else{
                    $usuario = $_SESSION['usuario'];
                    $admin = $_SESSION['admin'];
                    $_SESSION['usuario'];
                    $_SESSION['admin'];  
                }
            }  

        }else{
            //echo "5";
            session_unset();
            session_destroy(); 
            header("Location: ../../../../../index.php");  
        }
    
    }else{
        //echo "6";
        session_unset();
        session_destroy(); 
        header("Location: ../../../../../index.php");  
    }

    // Verifique se o ID da sessão e o ID do sócio foram passados na URL
    if(isset($_GET['id_sessao']) && isset($_GET['id_socio'])) {
        $id_sessao = $_SESSION['usuario'];
        $id_socio = intval($_GET['id_socio']);

    } else {
        // Caso os IDs não tenham sido passados na URL
        echo "IDs não encontrados na URL.";
    }

    $id_sessao = $_SESSION['usuario'];
    $sql_query = $mysqli->query("SELECT * FROM socios WHERE id = '$id_sessao'") or die($mysqli->$error);
    $usuario_sessao = $sql_query->fetch_assoc();

    $id_socio = intval($_GET['id_socio']);
    $sql_socio = $mysqli->query("SELECT * FROM socios WHERE id = '$id_socio'") or die($mysqli->$error);
    $socio = $sql_socio->fetch_assoc();

    $foto = $socio['foto'];
    
    if($foto != ''){
        $foto = '../../usuarios/arquivos/'. $foto;
    }else{
        $foto = '../../arquivos_fixos/9734564-default-avatar-profile-icon-of-social-media-user-vetor.jpg';
    }

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            width: 50%;
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
    <title>Descrição do Sócio</title>
</head>
<body>
    <form id="iform" action="detalhe_altera.php" method="POST" enctype="multipart/form-data" autocomplete="on" onsubmit="return validarFormulario()">
        
        <p>
            <img id="ifoto" style="max-width: 200px;" src= "<?php echo $foto; ?>" name="foto" alt=""><br>
        </p>
        <input type="hidden" value="<?php echo $socio['id']; ?>" name="id" ><br> 
        <input type="hidden" value="<?php echo $id_sessao; ?>" name="idsessao" ><br> 
        <fieldset class="dados">
            <p>
                <label for="idata" >Data de Registro: </label>
                <?php
                    // Suponha que $socio seja um array contendo os dados do banco de dados, incluindo o campo "data_nascimento"
                    $data = $socio['data'];

                    // Formate a data para o formato brasileiro (dd/mm/yyyy)
                    $dataFormatada = date('d/m/Y', strtotime($data));
                ?>
                <input id="idata" value="<?php echo $dataFormatada; ?>" name="data" type="text" oninput="formatarData(this)" onblur="verificaData()"><br>
            </p>
            <p>
                <label for="iapelido" >Apelido: </label>
                <input disabled ="false" id="iapelido" value="<?php echo $socio['apelido']; ?>" name="apelido" type="text"><br>
            </p>        
            <p>
                <label for="inome_completo" >Nome Completo: </label>
                <input disabled ="false" id="inome_completo" value="<?php echo $socio['nome_completo']; ?>" name="nome_completo" type="text"><br>
            </p>
            <p>
                <label for="icpf" >CPF: </label>
                <input disabled ="false" id="icpf" value="<?php echo $socio['cpf']; ?>" name="cpf" type="text"><br>
            </p>
            <p>
                <label for="irg" >RG: </label>
                <input disabled ="false" id="irg" value="<?php echo $socio['rg']; ?>" name="rg" type="text"><br>
            </p>
            <p>
                <label for="inascimento" >Data de Nascimento: </label>
                <?php
                    // Suponha que $usuario seja um array contendo os dados do banco de dados, incluindo o campo "data_nascimento"
                    $dataNascimento = $socio['nascimento'];

                    // Formate a data para o formato brasileiro (dd/mm/yyyy)
                    $dataNascimentoFormatada = date('d/m/Y', strtotime($dataNascimento));
                ?>
                <input disabled ="false" id="inascimento" value="<?php echo $dataNascimentoFormatada; ?>" name="nascimento" type="text"><br>
            </p>
            <p>
                <label for="iuf">Estado Natal: </label>
                <input disabled ="false" id="iuf" value="<?php echo $socio['uf']; ?>" name="uf" type="text"><br>   
            </p>
            <p>
                <label for="icid_natal" >Cidade Natal: </label>
                <input disabled ="false" id="icid_natal" value="<?php echo $socio['cid_natal']; ?>" name="cidnatal" type="text"><br>
            </p>
            <p>
                <label for="imae">Nome da Mãe: </label>
                <input disabled ="false" id="imae" value="<?php echo $socio['mae']; ?>" name="mae" type="text"><br>
            </p>
            <p>
                <label for="ipai">Nome do Pai: </label>
                <input disabled ="false" id="ipai" value="<?php echo $socio['pai']; ?>" name="pai" type="text"><br>
            </p>
            <p>
                <label for="isexo">Sexo: </label>
                <input disabled ="false" id="isexo" value="<?php echo $socio['sexo']; ?>" name="sexo" type="text"><br>
            </p>
        </fieldset> 
        <fieldset class="endereco">
            <legend>Endereço Atual</legend>
            <p> 
                <label for="iuf_atual">Estado Atual: </label>
                <input disabled ="false" value="<?php echo $socio['uf_atual']; ?>" name="uf_atual" id="iuf_atual" type="text"><br>
            </p>
            <p>
                <label for="icep">CEP: </label>
                <input disabled ="false" value="<?php echo $socio['cep']; ?>" name="cep" id="icep" type="text"><br>
            </p>
            <p>
                <label for="icid_atual">Cidade Atual: </label>
                <input disabled ="false" value="<?php echo $socio['cid_atual']; ?>" name="cid_atual" id="icid_atual" type="text"><br>
            </p>
            <p>
                <label for="iendereco">Logradouro: AV/RUA </label>
                <input disabled ="false" value="<?php echo $socio['endereco']; ?>" name="endereco" id="iendereco" type="text"><br>
            </p>
            <p>
                <label id="" for="inum">N°: </label>
                <input disabled ="false" value="<?php echo $socio['numero']; ?>" name="numero" id="inum" type="text"><br>
            </p>
            <p>
                <label id="" for="ibairro">Bairro: </label>
                <input disabled ="false" value="<?php echo $socio['bairro']; ?>" name="bairro" id="ibairro" type="text"><br>
            </p>
        </fieldset>
        <fieldset class="contatos">
            <legend>Contatos</legend>
            <p>
                <label id="" for="icelular1">Celular 1: </label>
                <input disabled ="false" value="<?php echo $socio['celular1']; ?>" name="celular1" id="icelular1" type="text" size=""><br>
            </p>
            <p>
                <label id="" for="icelular2">Celular 2: Opcional </label>
                <input disabled ="false" value="<?php echo $socio['celular2']; ?>" name="celular2" id="icelular2" type="text" size=""><br>
            </p>
            <p>
                <label id="" for="iemail">E-mail:</label>
                <input disabled ="false" value="<?php echo $socio['email']; ?>" name="email" id="iemail" type="email"><br>
            </p>
        </fieldset>
        <fieldset>
            <legend>Status</legend>
            <p>
                <?php
                    // Suponha que $socio seja um array contendo os dados do banco de dados, incluindo o campo "sexo"
                    $ativo = ($socio['status'] == 'ATIVO') ? 'checked' : '';
                    $suspenso = ($socio['status'] == 'SUSPENSO') ? 'checked' : '';
                    $afastado = ($socio['status'] == 'AFASTADO') ? 'checked' : '';
                    $excluido = ($socio['status'] == 'EXCLUIDO') ? 'checked' : '';
                ?>
                <input type="radio" name="status" id="iativo" value="ATIVO"<?php echo $ativo; ?>><label class="radio" for="iativo">ATIVO</label> 
                <input type="radio" name="status" id="isuspenso" value="SUSPENSO"<?php echo $suspenso; ?>><label class="radio" for="isuspenso">SUSPENSO</label> 
                <input type="radio" name="status" id="iafastado" value="AFASTADO"<?php echo $afastado; ?>><label class="radio" for="iafastado">AFASTADO</label> 
                <input type="radio" name="status" id="iexcluido" value="EXCLUIDO"<?php echo $excluido; ?>><label class="radio" for="iexcluido">EXCLUIDO</label>
            </p>
            <p>
                <label for="iobs">Obs.: </label><br>
                <textarea required type="text" name="obs" id="iobs" ><?php echo $socio['observacao']; ?></textarea>
            </p>
        </fieldset>
        <fieldset>
            <legend>Torná-lo administrador</legend>
            <p>
                <p>Caso você, <?php echo $usuario_sessao['apelido']; ?>, adicionar o <?php echo $socio['apelido']; ?> como administrador, você será desconectado e não sera mais administrador 
                    ao confirmar e salvar. Você só podera ser administrador novamente se o <?php echo $socio['apelido']; ?> te colocar de volta na 
                    administração.
                </p>
                <?php
                    // Suponha que $socio seja um array contendo os dados do banco de dados, incluindo o campo 
                    //echo $usuario['admin'];
                    $usuario_normal = ($socio['admin'] == 0) ? 'checked' : '';
                    $administrador = ($socio['admin'] == 1) ? 'checked' : '';
                ?>
                <input type="radio" name="admin" id="iusuario" value="0" <?php echo $usuario_normal; ?>><label class="radio" for="iusuario">Usuário</label>
                <input type="radio" name="admin" id="iadmin" value="1" <?php echo $administrador; ?>><label class="radio" for="iadmin">Administrador</label> 
            </p>
        </fieldset>
        <p>
            <label for="imotivo">Motivo ao qual quiz se associar: </label><br>
            <textarea disabled ="false" type="text" name="motivo" id="imotivo" ><?php echo $socio['motivo']; ?></textarea>
        </p>
        <p>
            <label for="itermos">Termos: </label><br>
            <textarea disabled ="false" type="text" name="termos" id="itermos" ><?php echo $socio['termos']; ?></textarea>
        </p>
        <p>
            <span id="imgAlerta"></span><br>
            <span id="imgAlerta2" type="hidden"></span><br>
            <a href="listaSocios.php" style="margin-left: 10%; margin-right: 10%;">Voltar</a>
            <button id="" type="submit" style="margin-left: 10%;">Salvar</button>
        </p>
        <script src="verifica_data.js"></script>
    </form>
</body>
</html>

