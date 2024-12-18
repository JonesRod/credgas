<?php
include('../../conexao.php');
session_start();

// Verifica se o usuário está autenticado
if (!isset($_SESSION['id'])) {
    header("Location: ../../index.php");
    exit();
}

$id = $_SESSION['id'];
$sql_query = $mysqli->query("SELECT * FROM meus_clientes WHERE id = '$id'");
$dados = $sql_query->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Contatos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f5f5f5;
            padding: 20px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        form {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            color: #333;
        }

        input:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        .button-container {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        button {
            flex: 1;
            padding: 10px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button[type="submit"] {
            background: #007bff;
            color: white;
        }

        button[type="submit"]:hover {
            background: #0056b3;
        }

        button.cancelar {
            background: #dc3545;
            color: white;
        }

        button.cancelar:hover {
            background: #a71d2a;
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            form {
                padding: 15px;
            }

            button {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <form action="processa_alteracao.php" method="POST">
        <h1>Alterar Contatos</h1>
        
        <label for="telefone1">Telefone (WhatsApp):</label>
        <input type="text" id="telefone1" name="telefone1" value="<?php echo $dados['celular1']; ?>" oninput="formatarCelular(this)" onblur="verificaCelular1()">

        <label for="telefone2">Telefone Adicional (Opcional):</label>
        <input type="text" id="telefone2" name="telefone2" value="<?php echo $dados['celular2']; ?>" oninput="formatarCelular(this)" onblur="verificaCelular2()">

        <label for="email">E-mail:</label>
        <input type="email" id="email" name="email" value="<?php echo $dados['email']; ?>">

        <div class="button-container">
            <button type="submit">Salvar Alterações</button>
            <button type="button" class="cancelar" onclick="window.location.href='perfil_cliente.php'">Cancelar</button>
        </div>
    </form>
</body>
    <script>
        function formatarCelular(input) {
            let value = input.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
            if (value.length > 11) {
                value = value.substr(0, 11);
            }
            if (value.length > 10) {
                value = value.replace(/(\d{1})(\d{1})(\d{5})/, '($1$2) $3-');
            } else if (value.length > 6) {
                value = value.replace(/(\d{1})(\d{1})(\d{4})/, '($1$2) $3-');
            } else if (value.length > 2) {
                value = value.replace(/(\d{1})(\d{1})/, '($1$2) ');
            }else if (value.length > 2) {
                value = value.replace(/(\d{1})(\d{1})/, '($1$2) ');
            }else if (value.length > 1) {
                value = value.replace(/(\d{1})/, '($1');
            }
            input.value = value;
        }
        function verificaCelular1(){
            var celular =document.getElementById('telefoneComercial').value;
            //console.log(celular.length);
            if(celular.length < 15 ){
                
                document.querySelector('#msgAlerta').textContent = "Preencha o campo Celular corretamente!";
                document.getElementById('telefoneComercial').focus();
            }else{
                document.querySelector('#msgAlerta').textContent = "";
            }
        }
        function verificaCelular2(){
            var celular =document.getElementById('telefoneResponsavel').value;
            //console.log(celular.length);
            if(celular.length < 15 ){
                
                document.querySelector('#msgAlerta').textContent = "Preencha o campo Celular corretamente!";
                document.getElementById('telefoneResponsavel').focus();
            }else{
                document.querySelector('#msgAlerta').textContent = "";
            }
        }
    </script>
</html>
