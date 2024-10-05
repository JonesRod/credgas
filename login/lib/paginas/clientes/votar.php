<?php
    include('../../conexao.php');

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
                    header("Location: ../usuarios/usuario_home.php");      
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
            header("Location: ../../../../index.php");  
        }

    }else{
        //echo "6";
        session_unset();
        session_destroy(); 
        header("Location: ../../../../index.php");  
    }

    $id = $_SESSION['usuario'];
    $sql_query = $mysqli->query("SELECT * FROM socios WHERE id = '$id'") or die($mysqli->$error);
    $usuario = $sql_query->fetch_assoc();


    $id_inscrito = $_GET['id_inscrito'];
    $sql_int_associar = $mysqli->query("SELECT * FROM int_associar WHERE id = '$id_inscrito'") or die($mysqli->error);
    

    if ($sql_int_associar && $sql_int_associar->num_rows > 0) {
        $inscrito = $sql_int_associar->fetch_assoc();

        $usuario = $usuario['id'];
        $id_inscrito = $inscrito['id'];
        $foto = $inscrito['foto'];
        $apelido = $inscrito['apelido'];
        $nome = $inscrito['nome_completo'];

        // Calcula a idade a partir da data de nascimento
        $dataNascimento = new DateTime($inscrito["nascimento"]);
        $hoje = new DateTime();
        $idade = $dataNascimento->diff($hoje)->y;

        $motivo = $inscrito['motivo'];

    } else {
        echo "Inscrito não encontrada. Por favor, atualize a pagina anterior e tente novamente. <br>";
        unset($_POST);
        header("refresh: 5; inicio.php");
        echo "<script>
            setTimeout(function() {
                window.close();
            }, 5000);
        </script>";
        die();
    }
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            text-align: center;
            max-width: 800px;
            margin: auto;
            font-family: Arial, sans-serif;
        }
        .info {
            margin-top: 10px;
            margin-left: 10px;
            text-align: left; /* Alinha o texto à esquerda dentro da div */
            display: inline-block; /* Faz com que a div se comporte como um elemento em linha */
            vertical-align: middle; /* Alinha verticalmente ao centro */
        }
        .info input{
            margin-bottom: 15px;
            /*display: block; /* Faz com que as labels se comportem como blocos para garantir que cada uma fique em uma linha */
        }     
        form {
            margin-top: 20px;
        }
       /* img {
            max-width: 100%;
        }*/

        textarea {
            width: 80%;
            max-width: 80%;
            box-sizing: border-box;
            margin-top: 10px;
        }
        /*div {
            margin-top: 10px;
        }*/
        .radio label{
            display: inline-block; /* Alinha as labels dos radios em linha */
            margin-bottom: 20px; /* Adiciona um espaço entre as labels e os radios */
        }
        button[type="submit"] {
        margin-top: 10px; /* Adicione a quantidade de espaço desejada */
        }
    </style>
    <script>
        function validarFormulario() {
            var votoSelecionado = document.querySelector('input[name="voto"]:checked');

            if (votoSelecionado) {
                return true; // Se um voto foi selecionado, o formulário será enviado
            } else {
                alert('Por favor, selecione uma opção de voto.');
                return false; // Se nenhum voto foi selecionado, o formulário não será enviado
            }
        }
    </script>

    <title>Votação</title>
</head>
<body>

    <h1>Votação</h1>

    <img src="../usuarios/<?php echo $foto; ?>" alt="Foto" width="100"><br>

    <div class="info">
        <label for="">Apelido: </label><br>
        <input disabled ="false" type="text" value="<?php echo $apelido; ?>"><br>
        <label for="">Nome Completo: </label><br>
        <input disabled ="false" type="text" value="<?php echo $nome; ?>"><br>
        <label for="">Idade: </label><br>
        <input disabled ="false" type="text" value="<?php echo $idade; ?>"><br>
        <label for="">Motivo por querer se associar:</label> <br>
    </div>
    
    <textarea name="" id="" cols="30" rows="10"><?php echo $motivo; ?></textarea><br>

    <form action="registrar_voto.php" method="POST" onsubmit="return validarFormulario()">

        <input type="hidden" name="usuario" value="<?php echo $usuario; ?>">
        <input type="hidden" name="id_inscrito" value="<?php echo $id_inscrito; ?>">

        <div class="radio">
            <label id="ipergunta">Você aprova este inscrito?</label><br>
            <input type="radio" name="voto" id="isim" value="SIM"><label for="isim">SIM</label>
            <input type="radio" name="voto" id="inao" value="NAO"><label for="inao">NÃO</label>
        </div>

        <button type="submit">Confirmar Voto</button>
    </form>

</body>
</html>