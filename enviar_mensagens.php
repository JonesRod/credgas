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

$data_atual = date('Y-m-d');

// Prepara a consulta SQL para evitar SQL Injection
$stmt = $conn->prepare("SELECT * FROM contatos WHERE data_envio = ?");
$stmt->bind_param('s', $data_atual); // 's' indica string
$stmt->execute();
$db_result = $stmt->get_result();

$success_count = 0;
$failure_count = 0;

if ($db_result->num_rows > 0) {
    $instance_id = '3D5BF48FE753C08852A4C69C4440B4F5'; // SEU_ID_INSTANCIA
    $token = '07FE974114D13C216D907D05'; // SEU_TOKEN_ZAPI

    while($row = $db_result->fetch_assoc()) {
        $telefone = $row['telefone'];
        $mensagem = "Olá " . $row['nome'] . ", esta é uma mensagem automática.";

        $url = "https://api.z-api.io/instances/$instance_id/token/$token/send-messages";
        $data = [
            "phone" => $telefone,
            "message" => $mensagem
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
            ],
        ];

        $context  = stream_context_create($options);
        $api_result = file_get_contents($url, false, $context);

        if ($api_result === FALSE) {
            $failure_count++;
        } else {
            // Exibe a resposta da API para verificar sucesso ou falha
            $api_response = json_decode($api_result, true);
            
            if (isset($api_response['zaapId']) && isset($api_response['messageId'])) {
                $success_count++;
            } else {
                $failure_count++;
            }
        }
    }
    
    // Mensagem final
    echo "Total de mensagens enviadas com sucesso: $success_count<br>";
    echo "Total de falhas ao enviar mensagens: $failure_count<br>";

} else {
    echo "Nenhuma mensagem para enviar hoje.";
}

$stmt->close();
$conn->close();
?>
