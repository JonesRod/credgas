<?php
    //acesso do banco root
    /*$host = "localhost";
    $usuario = "root";
    $senha = "";
    $banco = "jr_comercio";

    //acesso do banco no site
    /*$host = "localhost";
    $usuario = "id21385241_usuario40ribas";
    $senha = "Batata/2023";
    $banco = "id21385241_banco40ribas";*/

    /*$mysqli = new mysqli(hostname: $host, username: $usuario, password: $senha, database: $banco);*/



    $host = "localhost";
    $usuario = "root";
    $senha = "";
    $banco = "jr_comercio";

    $mysqli = new mysqli($host, $usuario, $senha, $banco);

    // Verifica se houve erro na conexão
    if ($mysqli->connect_error) {
        die("Falha na conexão: " . $mysqli->connect_error);
    } else {
        //echo "Conexão bem-sucedida!";
    }

?>