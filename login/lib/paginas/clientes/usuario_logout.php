<?php

if(!isset($_SESSION)) 
    session_start();

if(isset($_SESSION)){
    //echo "ee";
    session_unset();
    session_destroy();  
    header("Location: ../../../../index.php");            
}    
?>