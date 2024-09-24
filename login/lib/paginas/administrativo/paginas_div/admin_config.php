<?php
    include('../../../conexao.php');

    if(!isset($_SESSION)){
        session_start(); 

        if(isset($_SESSION['usuario'])){
            $usuario = $_SESSION['usuario'];
            $admin = $_SESSION['admin'];
            $_SESSION['usuario'];
            $_SESSION['admin']; 

            $id = $_SESSION['usuario'];
            $sql_query = $mysqli->query("SELECT * FROM socios WHERE id = '$id'") or die($mysqli->$error);
            $usuario = $sql_query->fetch_assoc(); 

            $id = '1';
            $dados = $mysqli->query("SELECT * FROM config_admin WHERE id = '$id'") or die($mysqli->$error);
            $dadosEscolhido = $dados->fetch_assoc();
        
            //$logo = $dadosEscolhido['logo'];
            if(isset($dadosEscolhido['logo'])) {
                $logo = $dadosEscolhido['logo'];
                
                if($logo == ''){
                    $logo = '../../arquivos_fixos/IMG-20230811-WA0040.jpg';
                }else{
                    $logo = '../arquivos/IMG-20230811-WA0040.jpg';
                }
            }
            $mysqli->close();
        }else{
            session_unset();
            session_destroy();
            header("Location: ../admin_logout.php");             
        }
    }else{    
        if(isset($_SESSION['usuario'])){
            $usuario = $_SESSION['usuario'];
            $admin = $_SESSION['admin'];
            $_SESSION['usuario'];
            $_SESSION['admin']; 

            $id = $_SESSION['usuario'];
            $sql_query = $mysqli->query("SELECT * FROM socios WHERE id = '$id'") or die($mysqli->$error);
            $usuario = $sql_query->fetch_assoc(); 

            $id = '1';
            $dados = $mysqli->query("SELECT * FROM config_admin WHERE id = '$id'") or die($mysqli->$error);
            $dadosEscolhido = $dados->fetch_assoc();
        
            //$logo = $dadosEscolhido['logo'];
            if(isset($dadosEscolhido['logo'])) {
                $logo = $dadosEscolhido['logo'];
                //echo 'ooi';
                if($logo == ''){
                    $logo = '../../arquivos_fixos/IMG-20230811-WA0040.jpg';
                }else{
                    $logo = '../arquivos/IMG-20230811-WA0040.jpg';
                }
            }
            $mysqli->close();
        }else{
            session_unset();
            session_destroy();
            header("Location: ../admin_logout.php");             
        }
    }

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="admin_config.css">
    <title>Configuração</title>
</head>
<body>

    <h1 id="ititulo" >Configurações do Administrador</h1>
    
    <form id="meuFormulario" action="admin_altera_dados.php" method="POST" enctype="multipart/form-data" autocomplete="on" onsubmit="return validateForm()">
        <fieldset class="partes">
            <p>
                <img class="imagens" id="ilogo" style="max-width: 200px;" src= "<?php echo $logo; ?>" name="logo_antiga" alt=""><br>
                <img class="imagens" id="ilogoNova" style="max-width: 200px;" alt=""><br>
                <label for="imageInput">Alterar Logo</label><br>
                <input type="file" id="imageInput" name="imageInput" accept=".png, .jpg, .jpeg" onchange="imgLogo(event)">
                <input type="hidden" name="end_logo" value= "<?php echo $logo; ?>">
            </p> 
            <input type="hidden" value="<?php echo $usuario['id']; ?>" name="admin">
            <p>
                <label for="irazao">Razão Social:</label><br>
                <input required name="razao" id="irazao" type="text" value="<?php echo $dadosEscolhido['razao']; ?>">           
            </p>
            <p>
                <label for="icnpj">CNPJ:</label><br>
                <input required name="cnpj" id="icnpj" type="text" oninput="formataCNPJ(this)" onblur="verificaCnpj()" value="<?php echo $dadosEscolhido['cnpj']; ?>">           
            </p>
        </fieldset>
        <fieldset class="partes">
            <legend>Localização:</legend>
            <p> 
                <label for="iuf">Estado: </label><br>
                <select name="uf" id="iuf">
                <?php
                    $estados = array(
                    'AC' => 'Acre',
                    'AL' => 'Alagoas',
                    'AP' => 'Amapá',
                    'AM' => 'Amazonas',
                    'BA' => 'Bahia',
                    'CE' => 'Ceará' ,
                    'DF' => 'Distrito Federal',
                    'ES' => 'Espírito Santo',
                    'GO' => 'Goiás',
                    'MA' => 'Maranhão',
                    'MS' => 'Mato Grosso do Sul',
                    'MT' => 'Mato Grosso',
                    'MG' => 'Minas Gerais',
                    'PA' => 'Pará',
                    'PB' => 'Paraíba',
                    'PR' => 'Paraná',
                    'PE' => 'Pernambuco',
                    'PI' => 'Piauí',
                    'RJ' => 'Rio de Janeiro',
                    'RN' => 'Rio Grande do Norte',
                    'RS' => 'Rio Grande do Sul',
                    'RO' => 'Rondônia',
                    'RR' => 'Roraima',
                    'SC' => 'Santa Catarina',
                    'SP' => 'São Paulo',
                    'SE' => 'Sergipe',
                    'TO' => 'Tocantins'
                    );

                    $ufSelecionada = $usuario['uf'];

                    echo '<option value="' . $ufSelecionada . '">' . $estados[$ufSelecionada] . '</option>';

                    foreach ($estados as $uf => $estado) {
                        if ($uf !== $ufSelecionada) {
                            echo '<option value="' . $uf . '">' . $estado . '</option>';
                        }
                    }           
                ?>
                <option value="Escolha">---Escolha---</option>
                <option value="AC">Acre</option>
                <option value="AL">Alagoas</option>
                <option value="AP">Amapá</option>
                <option value="AM">Amazonas</option>
                <option value="BA">Bahia</option>
                <option value="CE">Ceará</option>
                <option value="DF">Distrito Federal</option>
                <option value="ES">Espírito Santo</option>
                <option value="GO">Goiás</option>
                <option value="MA">Maranhão</option>
                <option value="MS">Mato Grosso do Sul</option>
                <option value="MT">Mato Grosso</option>
                <option value="MG">Minas Gerais</option>
                <option value="PA">Pará</option>
                <option value="PB">Paraíba</option>
                <option value="PR">Paraná</option>
                <option value="PE">Pernambuco</option>
                <option value="PI">Piauí</option>
                <option value="RJ">Rio de Janeiro</option>
                <option value="RN">Rio Grande do Norte</option>
                <option value="RS">Rio Grande do Sul</option>
                <option value="RO">Rondônia</option>
                <option value="RR">Roraima</option>
                <option value="SC">Santa Catarina</option>
                <option value="SP">São Paulo</option>
                <option value="SE">Sergipe</option>
                <option value="TO">Tocantins</option>
                </select>
            </p>
            <p>
                <label for="icep">CEP: </label><br>
                <input required name="cep" value="<?php echo $dadosEscolhido['cep']; ?>" name="cep" id="icep" type="text" maxlength="9" oninput="formatarCEP(this)" onblur="fetchCityByCEP()"><br>
            </p>
            <p>
                <label for="icidade">Cidade Atual: </label><br>
                <input required name="cid" id="icidade" type="text" value="<?php echo $dadosEscolhido['cid']; ?>"><br>
            </p>
            <p>
                <label for="iendereco">Logradouro: AV/RUA </label><br>
                <input required name="endereco" id="iendereco" type="text" value="<?php echo $dadosEscolhido['rua']; ?>"><br>
            </p>
            <p>
                <label for="inum">N°: </label><br>
                <input required name="numero" id="inum" type="text" value="<?php echo $dadosEscolhido['numero']; ?>"><br>
            </p>
            <p>
                <label for="ibairro">Bairro: </label><br>
                <input required name="bairro" id="ibairro" type="text" value="<?php echo $dadosEscolhido['bairro']; ?>"><br>
            </p>
        
        </fieldset>
        <fieldset class="partes">
            <legend>Administrativo:</legend>
            <p>
                <label for="inome">Nome do Tesoureiro(Admin):</label><br>
                <input required name="nome_tesoureiro" id="inome" type="text" value="<?php echo $dadosEscolhido['nome_tesoureiro']; ?>">           
            </p>            
            <p>
                <label for="ipresidente">Nome do Presidente:</label><br>
                <input required name="presidente" id="ipresidente" type="text" value="<?php echo $dadosEscolhido['presidente']; ?>">           
            </p>
            <p>
                <label for="ivicepresidente">Nome do Vice-Presidente:</label><br>
                <input required name="vice_presidente" id="ivicepresidente" type="text" value="<?php echo $dadosEscolhido['vice_presidente']; ?>">           
            </p>

            <p>
                <label for="iEmailSuporte">E-mail de notificação:</label><br>
                <input required name="email_suporte" id="iEmailSuporte" type="email" value="<?php echo $dadosEscolhido['email_suporte']; ?>"><br>
                <label for="iSenha">Senha:</label><br>
                <input required name="senha" id="iSenha" type="text" value="<?php echo $dadosEscolhido['senha']; ?>">
            </p>
        </fieldset>
        <fieldset class="partes">
            <legend>Config. Financeira:</legend>
            <p>
                <label for="idia">Dia de vencimento das Mensalidades:</label><br>
                <input required name="dia_fecha_mes" id="idia" type="number" value="<?php echo $dadosEscolhido['dia_fecha_mes']; ?>">  
            </p>
            <p>
                <label for="imensal">Valor das Mensalidades: </label><br>
                <input required name="valor_mensalidades" id="imensal" type="number" value="<?php echo $dadosEscolhido['valor_mensalidades']; ?>">  
            </p>
            <p>
                <label for="idesc">Desconto na mensalidade se pagar em dia:</label><br>
                <input required name="desconto_mensalidades" id="idesc" type="number" value="<?php echo $dadosEscolhido['desconto_mensalidades']; ?>">  
            </p>
            <p>
                <label for="imulta">Multa na Mensalidade após vencimento:</label><br>
                <input required name="multa" id="imulta" type="number" value="<?php echo $dadosEscolhido['multa']; ?>">  
            </p>
            <p>
                <label for="ijoia">Valor da Jóia: </label><br>
                <input required name="joia" id="ijoia" type="number" value="<?php echo $dadosEscolhido['joia']; ?>">  
            </p>
            <p>
                <label for="iparJoia">Parcelamento máximo da Jóia: </label><br>
                <input required name="parcela_joia" id="iparJoia" type="number" value="<?php echo $dadosEscolhido['parcela_joia']; ?>">  
            </p>
            <p>
                <label for="imes3">Quantidades de meses em atraso para suspenção das atividades do associado: </label><br>
                <input required name="meses_vence3" id="imes3" type="number" value="<?php echo $dadosEscolhido['meses_vence3']; ?>">  
            </p>
            <p>
                <label for="imes5">Quantidades de meses em atraso para exclusão do associado: </label><br>
                <input required name="meses_vence5" id="imes5" type="number" value="<?php echo $dadosEscolhido['meses_vence5']; ?>">  
            </p>
        </fieldset>
        <fieldset class="partes">
            <legend>Orientações e Regras</legend>
            <p>
                <label for="idade">Idade minima para ser um associado: </label><br>
                <input required name="idade_min" id="idade" type="number" value="<?php echo $dadosEscolhido['idade_minima']; ?>">  
            </p>
            <p>
                <label for="itermos">Termos da Inscrição:</label><br>
                <textarea required type="text" name="termos_insc" id="itermos" cols="40" rows="10" minlength="10"><?php echo $dadosEscolhido['termos_insc']; ?></textarea><br> 
            </p>
            <p>
                <label for="ivalidade">Tempo de validade da Inscrição:</label><br>
                <label for="ivalidade">Dias: </label><input required type="number" name="validade_insc" id="ivalidade" value="<?php echo $dadosEscolhido['validade_insc']; ?>"><br> 
            </p>
            <p>
                <label for="iEst">Estatuto interno:</label><br>
                <input type="hidden" name="estatuto" id="iEst" value="<?php echo $dadosEscolhido['estatuto_int']; ?>">
                <button type="button" id="ibaixar_estatuto" onclick="baixarArq_estatuto()">Baixar Estatuto Atual</button><br>
                <input type="file" accept=".pdf, .doc, .docx" name="novo_estatuto" id="inovo_estatuto"></p>
            </p>
            <p>
                <label for="iReg" for="">Regimento interno:</label><br>
                <input type="hidden" name="regimento" id="iReg" value="<?php echo $dadosEscolhido['reg_int']; ?>">
                <button type="button" id="ibaixar_regimento" onclick="baixarArq_regimento()">Baixar Regimento Atual</button><br>
                <input type="file" accept=".pdf, .doc, .docx" name="novo_regimento" id="inovo_regimento"></p>
            </p>
            <p>
                <span id="imsgAlerta"></span><br>
                <!-- Link de voltar -->
                <a href="inicio.php"  style="margin-left: 10px; margin-right: 10px;">Voltar</a>
                <!--onclick="perguntarSalvar(); return false;"-->
                <a href="resetar_excluirDados/backup.php"  style="margin-left: 10px; margin-right: 10px;">Backup</a>
                <a href="resetar_excluirDados/deletar_dados.php"  style="margin-left: 10px; margin-right: 10px;">Excluir Todos os Dados</a>
                <a href="importar_exportar/importar.php"  style="margin-left: 10px; margin-right: 10px;">Importar/Exportar</a>

                <button type="submit">Salvar</button>
            </p>
        </fieldset>
        <script src="admin_verifica.js"></script>
    </form>

</body>
</html>