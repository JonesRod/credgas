<?PHP
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

    $id = $_SESSION['usuario'];
    $sql_query = $mysqli->query("SELECT * FROM socios WHERE id = '$id'") or die($mysqli->$error);
    $usuario = $sql_query->fetch_assoc();

    $id_joia_config = '1';
    $sql_joia_config = $mysqli->query("SELECT * FROM config_admin WHERE id = '$id_joia_config'") or die($mysqli->$error);
    $valor_joia = $sql_joia_config->fetch_assoc();

    $joia =$valor_joia['joia'];
    $parcelas = $valor_joia['parcela_joia'];

    if(isset($_GET['id_socio'])) {
        $id_socio = $_GET['id_socio'];
        //echo $id_socio;
        $sql_joia_receber = $mysqli->query("SELECT * FROM socios WHERE id = '$id_socio'") or die($mysqli->error);
        $socio = $sql_joia_receber->fetch_assoc();

        $admin = $usuario['apelido'];
        $id_socio = $socio['id'];
        //echo $id_socio;
        $apelido = $socio['apelido'];
        $nome = $socio['nome_completo'];
        $celular1 = $socio['celular1'];
        $celular2 = $socio['celular2'];
        $email = $socio['email'];

    } else {
       
        $admin = $usuario['apelido']; 
        $id_socio = '';
        $apelido = '';
        $nome = '';
        $celular1 = '';
        $celular2 = '';
        $email = '';
    }

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carregar Jóia</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>

        $(document).ready(function() {
           // calcularValorParcelas();
            // Função para buscar os dados do sócio pelo nome
            function buscarDadosSocio(nomeSocio) {
                //console.log('oii');
                $.ajax({
                    type: 'GET',
                    url: 'buscar_dados.php',
                    data: { pesquisa: nomeSocio },
                    dataType: 'json',
                    success: function(dados) {
                        if (dados.msg) {
                            $('#imsg').text(dados.msg); // Exibe a mensagem no <span>
                        } else {
                            $('#imsg').text(''); // Limpa a mensagem se houver dados válidos
                            $('#iid').val(dados.id);
                            $('#iapelido').val(dados.apelido);
                            $('#inome').val(dados.nome_completo);
                            $('#icelular1').val(dados.celular1);
                            $('#icelular2').val(dados.celular2);
                            $('#iemail').val(dados.email);
                            //console.log('oii');
                        }
                    },
                });
            } 
            $('#buscarSocio').click(function() {
                var nomeSocio = $('#ipesquisa').val();
                buscarDadosSocio(nomeSocio);
            }); 

        });

    </script>
    <style>
        body{
            font-family: Arial, sans-serif;
            align-items: center;   
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        #idiv {
            position: absolute; /* Define a posição como absoluta */
            top: 55%; /* Ajusta a distância do topo para 50% da altura do elemento pai (body) */
            left: 50%; /* Ajusta a distância da esquerda para 50% da largura do elemento pai (body) */
            transform: translate(-50%, -50%);
            border: 1px solid black;
            width: 40%;
            max-width: 40%; /* A tabela não irá além de 100% da largura do contêiner pai */
            padding-bottom: 10px;
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        #idiv label{
            margin-left: 20px;
        }
        input {
            text-align: center;
        }
    </style>
</head>
<body>
    <div id="idiv">
        <h2>Carregar Jóia</h2>

        <label for="ipesquisa">Pesquisar por Nome: </label><br>
        <input id="ipesquisa" name="pesquisa" type="text">
        <button id="buscarSocio">Buscar</button>
        <span id="imsg"></span>

        <form action="parcelamento_joia.php" method="post">

            <input id="" value="<?php echo $admin; ?>" name="admin" type="hidden">
            <input id="iid" value="<?php echo $id_socio; ?>" name="id_socio" type="hidden">
            <p>
                <label for="iapelido" >Apelido: </label><br>
                <input readonly id="iapelido" value="<?php echo $apelido; ?>" name="apelido" type="text">
            </p>
            <p>
                <label for="inome" >Nome Completo: </label><br>
                <input readonly id="inome" value="<?php echo $nome; ?>" name="nome" type="text">
            </p>
            <p>
                <label for="icelular1">Celular 1:</label><br>
                <input readonly id="icelular1" type="text" name="celular1" value="<?php echo $celular1; ?>" >
            </p>
            <p>
                <label for="icelular2">Celular 2:</label><br>
                <input readonly id="icelular2" type="text" name="celular2" value="<?php echo $celular2; ?>" >
            </p>
            <p>
                <label for="iemail">E-mail:</label><br>
                <input readonly id="iemail" type="text" name="email" value="<?php echo $email; ?>" >
            </p>
            <p>
                <label for="ijoia">Valor da Joia:</label><br>
                <input readonly id="ijoia" type="text" name="valor_joia" value="<?php echo number_format($joia, 2, ',', '.'); ?>" >
            </p>
            <p>
                <label for="ientrada">Entrada:</label><br>
                <input required id="ientrada" type="text" name="entrada" value="0,00"  oninput="formatarEntrada(this)" onblur="calcularValorParcelas()">
            </p>
            <p>
                <label for="irest">Restante:</label><br>
                <input readonly id="irest" type="text" name="restante" value="<?php echo number_format($joia, 2, ',', '.'); ?>" >
            </p>
            <p>
                <label for="iparcelas">Quantidade de Parcelas:</label><br>
                <input required id="iparcelas" type="text" name="qt_parcelas" value="<?php echo $parcelas; ?>" oninput="formatarParcela(this)" onblur="calcularValorParcelas()">
            </p>
            <p>
                <label for="ivalor_parcelas">Valor da Parcelas:</label><br>
                <input  readonly id="ivalor_parcelas" name="valor_parcela" value="<?php echo number_format($joia/$parcelas, 2, ',', '.'); ?>" >
            </p>

            <a href="inicio.php" style="margin-right: 20px;"> Voltar</a><button type="submit">Registrar</button>

        </form>
        <script src="calcular.js"></script>
    </div>
    
</body>
</html>