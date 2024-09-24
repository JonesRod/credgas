<?php
    include('../../../../conexao.php');
    session_start(); // Iniciar a sessão

    // Limpar o buffer de saída
    ob_start();

    // Incluir a conexão com banco de dados
    //include_once "conexao.php";

    // Receber o arquivo do formulário
    $arquivo = $_FILES['arquivo'];
    //var_dump($arquivo);

    // Variáveis de validação
    $primeira_linha = true;
    $linhas_importadas = 0;
    $linhas_nao_importadas = 0;
    $usuarios_nao_importado = "";

    // Verificar se é arquivo csv
    if($arquivo['type'] == "text/csv"){

        // Ler os dados do arquivo
        $dados_arquivo = fopen($arquivo['tmp_name'], "r");

        // Percorrer os dados do arquivo
        while($linha = fgetcsv($dados_arquivo, 1000, ";")){

            // Como ignorar a primeira linha do Excel
            if($primeira_linha){
                $primeira_linha = false;
                continue;
            }

            // Atribui os valores a variáveis
            $data = $linha[0];
            $apelido = $linha[1];
            $nome = $linha[2];
            $cpf = $linha[3];
            $rg = $linha[4];

            $nascimento = $linha[5];
            $endereco = $linha[6];
            $numero = $linha[7];
            $bairro = $linha[8];
            $celular = $linha[9];

            $email = $linha[10];
            $joia = $linha[11];
            $status = $linha[12];
            $obs = $linha[13];

            // Convertendo a data no formato d/m/Y para Y-m-d
            $data = date("Y-m-d", strtotime(str_replace('/', '-', $data)));
            $nascimento = date("Y-m-d", strtotime(str_replace('/', '-', $nascimento)));

            // Inserir os dados no banco de dados
            $sql_inserir_socios = "INSERT INTO socios (data, apelido, nome_completo, cpf, rg,
            nascimento, endereco, numero, bairro, celular1, email, status, observacao, joia)
            VALUES ('$data', '$apelido', '$nome', '$cpf', '$rg', '$nascimento', '$endereco', '$numero',
            '$bairro', '$celular', '$email', '$status', '$obs', '$joia')";

            // Executar a query
            $result = $mysqli->query($sql_inserir_socios);

            if($result){
                $linhas_importadas++;
            }else{
                $linhas_nao_importadas++;
                $usuarios_nao_importado .= ", " . ($linha[0] ?? "NULL");
            }
        }

        fclose($dados_arquivo);

        // Criar a mensagem com os CPF dos usuários não cadastrados no banco de dados
        if(!empty($usuarios_nao_importado)){
            $usuarios_nao_importado = "Usuários não importados: $usuarios_nao_importado.";
        }

        // Mensagem de sucesso
        $_SESSION['msg'] = "<p style='color: green;'>$linhas_importadas linha(s) importa(s), $linhas_nao_importadas linha(s) não importada(s). $usuarios_nao_importado</p>";

        // Redirecionar o usuário
        header("Location: importar.php");

    }else{

        // Mensagem de erro
        $_SESSION['msg'] = "<p style='color: #f00;'>Necessário enviar arquivo csv!</p>";

        // Redirecionar o usuário
        header("Location: importar.php");
    }
?>
