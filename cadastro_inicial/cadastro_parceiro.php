<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="localizador.js" defer></script> <!-- Inclui o arquivo JS -->
    <title>Cadastro do Parceiro</title>
    <link rel="stylesheet" href="cadastro_parceiro.css">
</head>
<body>
    
    <form id="cadastroEmpresa" action="processa_cadastro.php" method="POST">

        <h2>Cadastre-se e comece a vender</h2>

        <a href="../index.php"><= Voltar</a><br>

        <span id="msgAlerta"></span><br>

        <label for="razao">Razão Social:</label>
        <input type="text" id="razao" name="razao" required>

        <label for="nomeFantasia">Nome Fantasia:</label>
        <input type="text" id="nomeFantasia" name="nomeFantasia" required>

        <label for="cnpj">CNPJ:</label>
        <input type="text" id="cnpj" name="cnpj" required oninput="formatCNPJ(this)" onblur="verificaCNPJ()">

        <label for="inscricaoEstadual">Inscrição Estadual:</label>
        <input type="text" id="inscricaoEstadual" name="inscricaoEstadual" required oninput="this.value = this.value.replace(/\D/g, '')">

        <label for="categoria">Categoria:</label>
        <input type="text" id="categoria" name="categoria" required>

        <label for="telefoneComercial">Telefone Comercial:</label>
        <input type="text" id="telefoneComercial" name="telefoneComercial" required placeholder="(00) 00000-0000" oninput="formatarCelular(this)" onblur="verificaCelular1()">

        <label for="telefoneResponsavel">Telefone do Responsável:</label>
        <input type="text" id="telefoneResponsavel" name="telefoneResponsavel" required placeholder="(00) 00000-0000" oninput="formatarCelular(this)" onblur="verificaCelular2()">
        
        <label for="email">E-mail:</label>
        <input required value="" name="email" id="email" type="email" required>

        <p id="status-localizacao">Localização: Ativa</p>

        <label for="cep">CEP:</label>
        <input required value="" name="cep" id="cep" type="text" maxlength="9" oninput="formatarCEP(this)" onblur="buscarCidadeUF()">

        <label for="uf">Estado: </label>
        <select required name="uf" id="uf">
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
        <!-- Adicione mais opções para outros estados aqui -->
        </select>

        <label for="cidade">Cidade:</label>
        <input type="text" id="cidade" name="cidade" required>

        <!--<input placeholder="Search" type="checkbox" id="aceito" onchange="verificarAceite()" name="aceito" value="sim" required>Eu aceito os <a href="termos.php" target="_blank">Termos.</a><br>-->
    <!-- Estilo para o checkbox com link -->
        <div class="checkbox-container">
            <input type="checkbox" id="aceito" name="aceito" value="sim" required onchange="verificarAceite()">
            <label for="aceito">Eu aceito os <a href="termos.php" target="_blank">Termos</a>.</label>
        </div>

        <button type="submit" id="cadastrar" disabled ="false">Cadastrar</button>
    </form>

    <script src="cadastro_parceiro.js"></script>
</body>
</html>
