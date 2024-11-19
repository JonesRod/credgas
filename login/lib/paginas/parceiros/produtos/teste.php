<div style="margin-bottom: 20px; text-align: center; display: flex; align-items: center; justify-content: center;">
    <label for="statusLoja" style="margin-right: 10px;">Status da Loja:</label>
    <label class="switch" style="margin-right: 10px;">
        <input type="checkbox" id="aberto_fehado" name="aberto_fehado" <?php echo $statusChecked; ?> onchange="salvarStatusLoja()">
        <span class="slider"></span>
    </label>
    <span id="statusLojaTexto"><?php echo $statusLoja; ?></span>
</div>

<script>
    function salvarStatusLoja() {
        // Captura o valor do checkbox (aberto ou fechado)
        var status = document.getElementById('aberto_fehado').checked ? 'Aberta' : 'Fechada';
        
        // Envia os dados para o servidor usando AJAX
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "salvar_status.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        // Envia o status da loja
        xhr.send("statusLoja=" + status);

        // Mostra uma mensagem de confirmação
        xhr.onload = function() {
            if (xhr.status == 200) {
                alert("Status da loja atualizado para " + status);
            } else {
                alert("Erro ao salvar o status da loja.");
            }
        };
    }
</script>
