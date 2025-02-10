<?php

include('../../conexao.php');

$id = $_SESSION['id'];
$sql_query = $mysqli->query("SELECT * FROM meus_parceiros WHERE id = '$id'") or die($mysqli->$error);
$dados = $sql_query->fetch_assoc();
$minhaLogo = $dados['logo'];

if ($minhaLogo !=''){
    // Se existe e não está vazio, atribui o valor à variável logo
    $logo = $dados['logo'];
    //echo ('oii').$logo;
} else {
    // Se não existe ou está vazio, define um valor padrão
    $logo = '../arquivos_fixos/icone_loja.jpg';
}

// Verifica se o valor 'aberto_fechado' está presente nos dados e atribui 'Aberto' ou 'Fechado'
$statusLoja =  $dados['aberto_fechado_manual'];
$statusChecked = $statusLoja === 'Aberto' ? 'checked' : ''; // Define 'checked' se a loja estiver aberta

// Verifica se o valor 'aberto_fechado' está presente nos dados e atribui 'Aberto' ou 'Fechado'
$statusLojaaut =  $dados['aberto_fechado_aut'];
$statusCheckedaut = $statusLojaaut === 'Ativado' ? 'checked' : ''; // Define 'checked' se a loja estiver aberta


// Simulação de dados recuperados do banco de dados
$horarios_json = $dados['horarios_funcionamento'];

// Converte JSON para array associativo
$horarios = json_decode($horarios_json, true);

// Obtém o dia da semana atual
$dias_semana = ["Domingo", "Segunda", "Terça", "Quarta", "Quinta", "Sexta", "Sábado"];
$dia_atual = $dias_semana[date('w')]; // Retorna o nome do dia da semana

// Obtém a hora atual
$hora_atual = date('H:i');

// Lógica para definir o status automaticamente
$statusAutomatico = "Fechado"; // Padrão caso os horários não sejam encontrados

// Verifica se o dia atual está na tabela de horários
if (isset($horarios[$dia_atual])) {
    $abertura = $horarios[$dia_atual]['abertura'];
    $fechamento = $horarios[$dia_atual]['fechamento'];
    $almoco_inicio = $horarios[$dia_atual]['almoco_inicio'];
    $almoco_fim = $horarios[$dia_atual]['almoco_fim'];

    // Verifica se está dentro do horário de funcionamento
    if ($hora_atual >= $abertura && $hora_atual <= $fechamento) {
        // Verifica se está no horário de almoço (se houver)
        if (!empty($almoco_inicio) && !empty($almoco_fim) && $hora_atual >= $almoco_inicio && $hora_atual <= $almoco_fim) {
            $statusAutomatico = "Fechado para almoço";
        } else {
            $statusAutomatico = "Aberto";
        }
    }
}

// Define o status final com base no modo ativado
if ($statusLojaaut === "Ativado") {
    $status = $statusAutomatico;
} else {
    $status = $statusLoja;
}

// Define a cor do status
$cor_status = ($status === "Aberto") ? "green" : "red";

?>
        <label for="status">
            Status de Funcionamento: 
            <span id="status" name="status" style="color: <?= $cor_status ?>;">
                <?= htmlspecialchars($status) ?>
            </span>
        </label>