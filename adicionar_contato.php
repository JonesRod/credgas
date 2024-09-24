<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "teste_zap";

// Cria a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $data_envio = $_POST['data_envio'];

    $sql = "INSERT INTO contatos (nome, telefone, data_envio) VALUES ('$nome', '$telefone', '$data_envio')";

    if ($conn->query($sql) === TRUE) {
        echo "Contato adicionado com sucesso!";
    } else {
        echo "Erro: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
