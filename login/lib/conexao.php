<?php
    //acesso do banco root
    $host = "localhost";
    $usuario = "root";
    $senha = "";
    $banco = "associacao_40ribas";

    //acesso do banco no site
    /*$host = "localhost";
    $usuario = "id21385241_usuario40ribas";
    $senha = "Batata/2023";
    $banco = "id21385241_banco40ribas";*/

    $mysqli = new mysqli($host, $usuario, $senha, $banco);

    /*function formatar_data($data){
        return implode('/', array_reverse(explode('-', $data)));
    }*/

    /*function formatar_telefone($telefone){
        $ddd = substr ($telefone, 0, 2);
        $parte1 = substr ($telefone, 2, 5);
        $parte2 = substr ($telefone, 7);
        return "($ddd) $parte1-$parte2";
    }*/
?>