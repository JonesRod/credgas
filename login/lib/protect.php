<?php

if(!isset($_SESSION['id'])) {
    session_start();
} else {
    session_unset();
    session_destroy();
    echo $_SESSION['id'];
    header("Location: ../index.php");
    die("Você não pode acessar esta página porque não está logado.<p><a href=\"../index.php\">Entrar</a></p>");
}

/*
if(!isset($_SESSION)){
    session_start();
    header("Location: ../index.php");
}
if(isset($_SESSION['admin'])){
    session_start();
    header("Location: paginas/admin_config.php");
}else{
    session_start();
    header("Location: paginas/home.php");
}*/

?>
