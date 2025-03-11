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
        // Tenta conexão com PDO
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$banco", $usuario, $senha);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //echo "Conexão com PDO bem-sucedida!";
        } catch (PDOException $e) {
            die("Falha na conexão com PDO: " . $e->getMessage());
        }
    } else {
        //echo "Conexão com mysqli bem-sucedida!";
    }

?>