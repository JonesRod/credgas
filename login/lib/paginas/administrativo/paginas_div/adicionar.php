<?php

    include('../../../conexao.php');

    if(!isset($_SESSION)){
        session_start(); 

        if(isset($_SESSION['usuario'])){

            if (isset($_POST["tipoLogin"])) {
                $usuario = $_SESSION['usuario'];
                $valorSelecionado = $_POST["tipoLogin"];
                $admin = $valorSelecionado;

                if($admin != 1){
                    header("Location: ../../usuarios/usuario_home.php");      
                } else {
                    $_SESSION['usuario'];
                    $_SESSION['admin'];  
                }
            }  

        } else {
            session_unset();
            session_destroy(); 
            header("Location: ../../../../../index.php");  
        }

    } else {
        session_unset();
        session_destroy(); 
        header("Location: ../../../../../index.php");  
    }

    $id = $_SESSION['usuario'];
    $sql_query = $mysqli->query("SELECT * FROM socios WHERE id = '$id'") or die($mysqli->$error);
    $usuario = $sql_query->fetch_assoc();

    $id_insc = $_GET['id_socio'];
    $sql_insc = $mysqli->query("SELECT * FROM int_associar WHERE id = '$id_insc'") or die($mysqli->$error);
    $inscrito = $sql_insc->fetch_assoc();

    if ($inscrito) {
        $admin = '0';
        $foto = $inscrito['foto'];
        $apelido = $inscrito['apelido'];
        $nome_completo = $inscrito['nome_completo'];
        $cpf = $inscrito['cpf'];
        $rg = $inscrito['rg'];
        $nascimento = $inscrito['nascimento'];
        $uf = $inscrito['uf'];
        $cid_nat = $inscrito['cid_natal'];
        $mae = $inscrito['mae'];
        $pai = $inscrito['pai'];
        $sexo = $inscrito['sexo'];
        $uf_atual = $inscrito['uf_atual'];
        $cep = $inscrito['cep'];
        $cid_atual = $inscrito['cid_atual'];
        $endereco = $inscrito['endereco'];
        $nu = $inscrito['nu'];
        $bairro = $inscrito['bairro'];
        $celular1 = $inscrito['celular1'];
        $celular2 = $inscrito['celular2'];
        $email = $inscrito['email'];
        $status = 'ATIVO'; // Defina o status conforme necessário
        $motivo = $inscrito['motivo'];
        $termos = $inscrito['termos'];
        $obs = $inscrito['observacao'];

        $sql_inserir_socios = "INSERT INTO socios (data, admin, foto, apelido, nome_completo, cpf,
        rg, nascimento, uf, cid_natal, mae, pai, sexo, uf_atual, cep, cid_atual, endereco, 
        numero, bairro, celular1, celular2, email, status, motivo, termos, observacao) 
        VALUES (NOW(), '$admin', '$foto', '$apelido', '$nome_completo', '$cpf',
        '$rg', '$nascimento', '$uf', '$cid_nat', '$mae', '$pai', '$sexo', '$uf_atual', 
        '$cep', '$cid_atual', '$endereco', '$nu', '$bairro', '$celular1', '$celular2', 
        '$email', '$status', '$motivo', '$termos', '$obs')";

        if ($mysqli->query($sql_inserir_socios) === TRUE) {
            $id_socio_inserido = $mysqli->insert_id;

            $sql_ultimo_mes = $mysqli->query("SELECT id, mensalidade_mes, mensalidade_ano FROM mensalidades_geradas ORDER BY data_vencimento DESC LIMIT 1") or die($mysqli->error);
            $ultimo_mes = $sql_ultimo_mes->fetch_assoc();
            
            $sql_dados = $mysqli->query("SELECT * FROM config_admin WHERE id = '1'") or die($mysqli->$error);
            $dados = $sql_dados->fetch_assoc();

            $valormes = $dados['valor_mensalidades']; // Substitua pelo valor desejado
            $diavenc = $dados['dia_fecha_mes']; // Substitua pelo dia desejado
            $desc = $dados['desconto_mensalidades']; // Substitua pelo desconto desejado
            $multa = $dados['multa']; // Substitua pela multa desejada
            $joia = $dados['joia'];
            $parcela_joia = $dados['parcela_joia'];

            if ($ultimo_mes) {
                $ultimo_id = $ultimo_mes['id'];
                $ultima_mensalidade = $ultimo_mes['mensalidade_mes'];
                $ultimo_ano = $ultimo_mes['mensalidade_ano'];
                //echo $ultima_mensalidade;
            } else {
                $mes_atual = date('n');
                $ano_atual = date('Y');
                $ultima_mensalidade = $mes_atual - 1;
                $ultimo_ano = $ano_atual;
                if($ultima_mensalidade < 1){
                    $ultima_mensalidade = 12;
                    $ultimo_ano = $ano_atual - 1;
                }
            }

            if ($id_socio_inserido) {
                $apelido = $inscrito['apelido'];
                $nome_completo = $inscrito['nome_completo'];
                $status = 'ATIVO';

                // Obter o dia de fechamento do mês (substitua 5 pelo dia desejado)
                $diaFechamentoMes = $diavenc + 10;

                // Obter o dia atual
                $diaAtual = date('d');

                $dataAtual = new DateTime();
                $ultimaMensalidade = new DateTime("$ultimo_ano-$ultima_mensalidade-$diavenc");
                $intervalo = date_diff($ultimaMensalidade, $dataAtual);
                $diferencaMeses = $intervalo->format('%m');
                $diferencaAnos = $intervalo->format('%y');
                $totalMeses = $diferencaAnos * 12 + $diferencaMeses;

                $qtmes = $totalMeses + 1;

                // Verificar se o dia atual é maior que o dia de fechamento do mês
                if ($diaAtual >= $diaFechamentoMes) {
                    //echo "Hoje é maior que o dia de fechamento do mês.";
                    //$qtmes = $qtmes;
                    $mes_atual = date('n');
                    $mes_atual =$mes_atual + 1;
                }else{
                    $qtmes = $qtmes + 1;
                    $mes_atual = date('n');
                }
                //echo $qtmes;
                $mes = $mes_atual + 1 ;
                $ano = $ultimo_ano;
                //echo $mes;
                //echo $qtmes;
                //die();
                if ($mes > 12) {
                    $mes = 1;
                    $ultimo_ano = $ultimo_ano + 1;
                }
                for ($i = 1; $i <= $qtmes; $i++) {
                    if ($mes > 12) {
                        $mes = 1;
                        $ano = $ano + 1;
                    }

                    $mensalidade = $mes - 1;

                    if ($mensalidade == 1) {
                        $ultimo_ano = $ultimo_ano + 1;
                    } 
                    if ($mensalidade < 1) {
                        $mensalidade = 12;
                    }

                    $data_vencimento = date("$ano-$mes-$diavenc");
                    //echo $data_vencimento;
                    $sql_mensalidade = "INSERT INTO mensalidades (id_socio, data, admin, apelido, nome_completo, status, mensalidade_dia, 
                    mensalidade_mes, mensalidade_ano, valor_mensalidade, data_vencimento, desconto_mensalidade, multa_mensalidade)
                    VALUES ('$id_socio_inserido', NOW(), '$admin', '$apelido', '$nome_completo', '$status', '$diavenc','$mensalidade', 
                    '$ultimo_ano', '$valormes', '$data_vencimento',  '$desc', '$multa')";
                    $mysqli->query($sql_mensalidade) or die($mysqli->error);

                    $sql_historico = "INSERT INTO historico_mensalidades (id_socio, data, admin, apelido, nome_completo, status, mensalidade_dia, 
                    mensalidade_mes, mensalidade_ano, valor_mensalidade, data_vencimento, desconto_mensalidade, multa_mensalidade)
                    VALUES ('$id_socio_inserido', NOW(), '$admin', '$apelido', '$nome_completo', '$status', '$diavenc','$mensalidade', 
                    '$ultimo_ano', '$valormes', '$data_vencimento',  '$desc', '$multa')";
                    $mysqli->query($sql_historico) or die($mysqli->error);

                    $mes++;
                }
                
                $sql_joia= "INSERT INTO joias_receber ( data, id_socio, admin, apelido, nome_completo)
                VALUES (NOW(), '$id_socio_inserido', '$admin', '$apelido', '$nome_completo')";
                $mysqli->query($sql_joia) or die($mysqli->error);

                // Executar a consulta SQL para excluir o inscrito
                $sql_excluir_int_associar = "DELETE FROM int_associar WHERE id = '$id_insc'";

                if ($mysqli->query($sql_excluir_int_associar)) {
                    
                } else {
                    echo "Erro ao excluir inscrito: " . $mysqli->error;
                }

                // Executar a consulta SQL para excluir o inscrito
                $sql_excluir_em_votacao = "DELETE FROM em_votacao WHERE id_inscrito = '$id_insc'";

                if ($mysqli->query($sql_excluir_em_votacao)) {
                    
                } else {
                    echo "Erro ao excluir Em Votação: " . $mysqli->error;
                }
                echo '<p><b>Sócio incluído com sucesso.</b></p>';
                header('refresh: 5; url=incluir_joia.php?id_socio=' . $id_socio_inserido);    
            }
        } else {
            echo "Erro ao inserir inscrito em socios: " . $mysqli_socios->error;
        }
        } else {
            echo "Erro ao obter dados do inscrito: " . $mysqli->$error;
    }

    $mysqli->close();
?>
