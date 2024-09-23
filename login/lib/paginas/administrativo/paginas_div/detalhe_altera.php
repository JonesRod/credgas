<?php
    include('../../../conexao.php');

    $erro = false;

    if(!isset($_SESSION))
        session_start();
        
    if(!isset($_SESSION['usuario'])){
        header("Location: ../../../../../index.php");
    }

    if(count($_POST) > 0) {
        $id_sessao = $_SESSION['usuario']; 
        $id = intval($_POST['id']);
        $data = $mysqli->escape_string($_POST['data']);
        $status = $mysqli->escape_string($_POST['status']);
        $admin = $mysqli->escape_string($_POST['admin']);
        $obs = $mysqli->escape_string($_POST['obs']);
        
        $dataStr = $data;
        $dataFormatada = DateTime::createFromFormat('d/m/Y', $dataStr);

        $dataFormatada = $dataFormatada->format('Y-m-d');

        // Verifica se o ID existe no banco de dados
        $result = $mysqli->query("SELECT * FROM socios WHERE id = $id");
    
        if($result->num_rows > 0) {
            // O ID existe, então pode prosseguir com a atualização
    
            if($admin == 1) {
                // Verifica se há mais de um sócio no banco de dados
                /*$result = $mysqli->query("SELECT COUNT(*) as total FROM socios");
                $row = $result->fetch_assoc();*/

                $sql_socio = $mysqli->query("SELECT * FROM socios WHERE id = '$id'") or die($mysqli->$error);
                $socio = $sql_socio->fetch_assoc();

                $result = $mysqli->query("SELECT COUNT(*) as total FROM socios WHERE admin = 1");
                $totalAdmins = $result->fetch_assoc()['total'];
    
                if($totalAdmins == 2 && $socio['admin'] == 1 && $socio['id'] == $id_sessao) {
                    $sql_code = "UPDATE socios
                    SET 
                    data = '$dataFormatada',
                    admin = '1',
                    status ='$status',
                    observacao = '$obs'
                    WHERE id = '$id'";
                    
                    $mysqli->query($sql_code) or die($mysqli->$erro);
                    
                    // Caso $admin seja 0, não há necessidade de confirmação
                    $msg = '<p><b>Dados atualizados com sucesso!</b></p>';
                    unset($_POST);
                    header('refresh: 5; listaSocios.php');

                } else if($totalAdmins == 2 && $socio['admin'] == 1 && $socio['id'] != $id_sessao) {
                    $sql_code = "UPDATE socios
                    SET 
                    data = '$dataFormatada',
                    admin = '1',
                    status ='$status',
                    observacao = '$obs'
                    WHERE id = '$id'";
                    
                    $mysqli->query($sql_code) or die($mysqli->$erro);
                    
                    // Caso $admin seja 0, não há necessidade de confirmação
                    $msg = "Dados registrados, mas não é possível registrar mais de 2 administradores no sistema!";
                    unset($_POST);
                    header('refresh: 5; listaSocios.php');
                    
                } else if($totalAdmins < 2 && $socio['id'] != $id_sessao) {
                    $msg = '<div id="confirmDialog" style="display:block;">
                    <p>Tem certeza que deseja tornar este usuário um administrador?</p>
                    <a id="confirmSim" href="#">Sim</a>
                    <a id="confirmNao" href="#">Não</a>
                    <a href="listaSocios.php">Cancelar</a>
                </div>';
                }

            } else {
                $sql_socio = $mysqli->query("SELECT * FROM socios WHERE id = '$id'") or die($mysqli->$error);
                $socio = $sql_socio->fetch_assoc();

                $result = $mysqli->query("SELECT COUNT(*) as total FROM socios WHERE admin = 1");
                $totalAdmins = $result->fetch_assoc()['total'];
    
                if($totalAdmins == 2 && $socio['admin'] == 1 && $socio['id'] == $id_sessao) {
                    $sql_code = "UPDATE socios
                    SET 
                    data = '$dataFormatada',
                    admin = '0',
                    status ='$status',
                    observacao = '$obs'
                    WHERE id = '$id'";
                    
                    $mysqli->query($sql_code) or die($mysqli->$erro);
                    
                    // Caso $admin seja 0, não há necessidade de confirmação
                    $msg = '<p><b>Dados atualizados com sucesso!</b></p>';
                    unset($_POST);
                    header('refresh: 5; ../admin_logout.php');

                } else if($totalAdmins >= 2 && $socio['id'] != $id_sessao) {
                    $sql_code = "UPDATE socios
                    SET 
                    data = '$dataFormatada',
                    admin = '0',
                    status ='$status',
                    observacao = '$obs'
                    WHERE id = '$id'";
                    
                    $mysqli->query($sql_code) or die($mysqli->$erro);
                    
                    // Caso $admin seja 0, não há necessidade de confirmação
                    $msg = '<p><b>Dados atualizados com sucesso!</b></p>';
                    unset($_POST);
                    header('refresh: 5; listaSocios.php');
                    
                } else if($totalAdmins < 2 && $socio['id'] == $id_sessao) {
                    $sql_code = "UPDATE socios
                    SET 
                    data = '$dataFormatada',
                    admin = '1',
                    status ='$status',
                    observacao = '$obs'
                    WHERE id = '$id'";
                    
                    $mysqli->query($sql_code) or die($mysqli->$erro);
                    
                    // Caso $admin seja 0, não há necessidade de confirmação
                    $msg = "Dados registrados, mas o usuario continuará sendo 1 administradores no sistema!";
                    unset($_POST);
                    header('refresh: 5; listaSocios.php');

                }

            }
        } else {
            $msg = "ID inválido.";
        }
    }
       
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body{
            text-align: center;
            margin-top: 30px;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var confirmSim = document.getElementById('confirmSim');
            var confirmNao = document.getElementById('confirmNao');

            if(confirmSim && confirmNao) {
                confirmSim.addEventListener('click', function(e) {
                    e.preventDefault();
                    window.location.href = 'atualizar_usuario.php?id=<?= $id ?>&status=<?= $status ?>&admin=1&obs=<?= $obs ?>&data=<?= $dataFormatada ?>';
                });

                confirmNao.addEventListener('click', function(e) {
                    e.preventDefault();
                    window.location.href = 'atualizar_usuario.php?id=<?= $id ?>&status=<?= $status ?>&admin=0&obs=<?= $obs ?>&data=<?= $dataFormatada ?>';
                });
            }
        });
    </script>
    <title></title>
</head>
<body>
    <div>
        <?php 
            echo $msg;
        ?>        
    </div>
</body>
</html>

