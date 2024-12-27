<?php
include('../../conexao.php');
session_start();

// Verifica se o usuário está autenticado
if (!isset($_SESSION['id'])) {
    header("Location: ../../index.php");
    exit();
}

// Obtém o ID do usuário autenticado
$id = $_SESSION['id'];
$id_cliente = $_GET['id'];


// Consulta para verificar se o cliente já possui crediário e buscar seus detalhes
$sql_query = $mysqli->prepare("SELECT * FROM meus_clientes WHERE id = ?");
$sql_query->bind_param('i', $id_cliente);
$sql_query->execute();
$result = $sql_query->get_result();
$crediario = $result->fetch_assoc();

// Calcula a idade
$hoje = new DateTime(); // Data atual
$dataNascimento = new DateTime($crediario['nascimento']); // Converte a data de nascimento
$idade = $hoje->diff($dataNascimento)->y; // Calcula a diferença em anos

$frente = htmlspecialchars($crediario['img_frente']); 
$verso = htmlspecialchars($crediario['img_verso']);
$self = htmlspecialchars($crediario['img_self']);

if ($frente !=''){
    // Se existe e não está vazio, atribui o valor à variável logo
    $frente = "../clientes/arquivos/".htmlspecialchars($crediario['img_frente']);
    //echo ('oii1').$frente;
} else {
    // Se não existe ou está vazio, define um valor padrão
    $frente = '';
    //echo ('oii2').$frente;
}

if ($verso !=''){
    // Se existe e não está vazio, atribui o valor à variável logo
    $verso = "../clientes/arquivos/".htmlspecialchars($crediario['img_verso']);
    //echo ('oii1').$frente;
} else {
    // Se não existe ou está vazio, define um valor padrão
    $verso = '';
    //echo ('oii2').$frente;
}
if ($self !=''){
    // Se existe e não está vazio, atribui o valor à variável logo
    $self = "../clientes/arquivos/".htmlspecialchars($crediario['img_self']);
    //echo ('oii1').$frente;
} else {
    // Se não existe ou está vazio, define um valor padrão
    $self = '';
    //echo ('oii2').$frente;
}


?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitação de Crediário</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        h1, h2 {
            text-align: center;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }


        input[type="text"], input[type="file"], button, a {
            text-align: center;
            width: 95%;
            padding: 10px;
            margin: 5px 0;
            border: 0px solid #ddd;
            border-radius: 5px;
        }
        input[type="text"]{
            text-align:left;
            border: 1px solid #ddd;
        }
        button {
            background-color: #007BFF;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .btn.cancel {
            background-color: #dc3545;
        }

        .btn.cancel:hover {
            background-color: #c82333;
        }

        .form-group-img, .video-container, .canvas-container, .preview-container {
            text-align: center;
            margin: 20px 0;
        }
        .video-container a{
            background-color: #fff;
            color: #0056b3;
            font-weight: bold;
            /*border: none;*/
            text-decoration-line: none;
            cursor: pointer;
            padding: auto;

        }
        .video-container a:hover{
            color:rgb(244, 149, 7);
            /*border: none;*/
            text-decoration-line: block;
        }

        video, canvas, img {
            width: 100%;
            max-width: 250px;
            border: 2px solid #ccc;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        .form-actions a{
            width: 50%;
            border-radius: 5px;
            background-color: #007BFF;
            text-decoration-line: none;
            color: #fff;

        }
        .form-actions a:hover{
            background-color: #0056b3;
        }
        .termos {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 20px;
        }
        .termos input {
            margin-top: -5px;
            margin-right: 10px; /* Espaço entre checkbox e texto */
        }
        .form-group-img {
    text-align: center; /* Alinha todo o conteúdo no centro */
}

.form-group-img img {
    display: block; /* Garante que a imagem seja tratada como bloco */
    margin: 0 auto; /* Centraliza horizontalmente */
}


        @media (max-width: 480px) {
            .form-actions {
                flex-direction: column;
            }

            button {
                width: 100%;
            }
            .form-actions a{
                width: 95%;

            }
        }
    </style>
</head>
<body>
    <div class="container">

        <h2>Dados do solicitante</h2>
        <hr>
        <p style="color:#007BFF;"><strong>Status:</strong> <?php echo htmlspecialchars($crediario['status_crediario']); ?> .</p>
        <p><strong>Nome:</strong> <?php echo htmlspecialchars($crediario['nome_completo']); ?></p>
        <p><strong>Data de Nascimento:</strong> <?php echo date('d/m/Y', strtotime($crediario['nascimento'])); ?></p>
        <p><strong>Idade:</strong> <?php echo $idade; ?> anos</p>
        <p><strong>CPF:</strong> <?php echo htmlspecialchars($crediario['cpf']); ?></p>
        <p><strong>Celular:</strong> <?php echo htmlspecialchars($crediario['celular1']); ?></p>
        <p><strong>CEP:</strong> <?php echo htmlspecialchars($crediario['cep']); ?></p>
        <p><strong>Estado:</strong> <?php echo htmlspecialchars($crediario['uf']); ?></p>
        <p><strong>Cidade:</strong> <?php echo htmlspecialchars($crediario['cidade']); ?></p>
        <p><strong>Endereco:</strong> <?php echo htmlspecialchars($crediario['endereco']); ?></p>
        <p><strong>Numero:</strong> <?php echo htmlspecialchars($crediario['numero']); ?></p>
        <p><strong>Bairro:</strong> <?php echo htmlspecialchars($crediario['bairro']); ?></p>
    
        
        <input type="hidden" name="id" required value="<?php echo $id; ?>">

        <div class="form-group-img">
            <label for="documento_foto_frente">Documento com Foto (Frente):</label>
            <?php if ($frente!=''):?>
                <img id="preview_frente" alt="Pré-visualização Frente" src="<?php echo $frente;?>">
            <?php else:?>
                <input type="file" id="documento_foto_frente" name="documento_foto_frente" accept="image/*,application/pdf">
                <img id="preview_frente" alt="Pré-visualização Frente" src="../clientes/arquivos/9734564-default-avatar-profile-icon-of-social-media-user-vetor.jpg">
            <?php endif; ?>
        </div>

        <div class="form-group-img">
            <label for="documento_foto_verso">Documento com Foto (Verso):</label>
            <?php if ($verso!=''):?>
                <img id="preview_verso" alt="Pré-visualização Verso" src="<?php echo $verso;?>">
            <?php else:?>
                <input type="file" id="documento_foto_verso" name="documento_foto_verso" accept="image/*,application/pdf" required>
                <img id="preview_verso" alt="Pré-visualização Verso" src="../clientes/arquivos/9734564-default-avatar-profile-icon-of-social-media-user-vetor.jpg">
            <?php endif; ?>
        </div>

        <h2>Selfie de Perfil</h2>
        <div class="video-container">
            <?php if ($self!=''):?>
                <img id="preview_self" alt="Pré-visualização self" src="<?php echo $self;?>"><br>
                <label for="aceito"><a href="termos_crediario.php" target="_blank">\\ Termos.</a></label><br>
                <a href="not_detalhes_crediario.php?id=<?php echo urlencode($id); ?>" class="">Voltar</a>
                <button type="button" id="consultar">Consultar SPC</button>

                <!-- Formulário com botões de Aprovar e Reprovar -->
                <form method="POST" class="botao" onsubmit="showLoading(event)">
                    <button type="submit" name="acao" value="aprovar">Aprovar</button>
                    <button type="submit" name="acao" value="reprovar">Reprovar</button>
                </form>

            <?php else:?>
                <video id="camera" autoplay></video>
                <button type="button" id="start-camera">Iniciar Câmera</button>
                <button type="button" id="capture" style="display: none;">Capturar</button>
        </div>

        <div class="canvas-container" style="display: none;">
            <canvas id="snapshot"></canvas>
            <button type="button" id="retake">Tirar Outra</button>
        </div>
            
        <input type="hidden" name="selfie_data" id="selfie_data" required>

        <div class="termos">
            <input type="checkbox" id="aceito" onchange="verificarAceite()" name="aceito" value="sim" required>
            <label for="aceito">Aceitar os <a href="termos_crediario.php" target="_blank">Termos.</a></label>
        </div>

        <div class="form-actions">
            <a href="not_detalhes_crediario.php" class="">Cancelar</a>
            <button type="submit" id="solicitar" disabled class="btn">Enviar Solicitação</button>
        </div>
            <?php endif; ?>
            
    </div>

    <script>
        const video = document.getElementById('camera');
        const startCameraButton = document.getElementById('start-camera');
        const captureButton = document.getElementById('capture');
        const retakeButton = document.getElementById('retake');
        const canvas = document.getElementById('snapshot');
        const previewFrente = document.getElementById('preview_frente');
        const previewVerso = document.getElementById('preview_verso');
        const documentoFrente = document.getElementById('documento_foto_frente');
        const documentoVerso = document.getElementById('documento_foto_verso');

        let stream = null;

        // Iniciar câmera
        startCameraButton.addEventListener('click', () => {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then((mediaStream) => {
                    stream = mediaStream;
                    video.srcObject = stream;
                    startCameraButton.style.display = "none";
                    captureButton.style.display = "inline-block";
                })
                .catch(() => alert("Não foi possível acessar a câmera."));
        });

        // Capturar selfie
        captureButton.addEventListener('click', () => {
            const context = canvas.getContext('2d');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            document.querySelector('.video-container').style.display = "none";
            document.querySelector('.canvas-container').style.display = "block";

            const dataURL = canvas.toDataURL("image/png");
            selfieDataInput.value = dataURL;

            if (stream) stream.getTracks().forEach(track => track.stop());
        });

        // Recomeçar selfie
        retakeButton.addEventListener('click', () => {
            document.querySelector('.canvas-container').style.display = "none";
            document.querySelector('.video-container').style.display = "block";
            startCameraButton.style.display = "inline-block";
        });

        // Pré-visualização do arquivo selecionado
        documentoFrente.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (file) {
                previewFrente.src = URL.createObjectURL(file);
                previewFrente.style.display = "block";
            }
        });

        documentoVerso.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (file) {
                previewVerso.src = URL.createObjectURL(file);
                previewVerso.style.display = "block";
            }
        });

        function verificarAceite() {
            var checkbox = document.getElementById('aceito');
            var botaoEnviar = document.getElementById('solicitar');
            
            if (checkbox.checked) {
                botaoEnviar.disabled = false;
            } else {
                botaoEnviar.disabled = true;
            }
            //console.log('oii');
        }

        const formulario = document.querySelector('form');
        const checkboxTermos = document.getElementById('aceito');
        const botaoEnviar = document.getElementById('solicitar');
        const selfieDataInput = document.getElementById('selfie_data'); // Campo hidden para selfie

        formulario.addEventListener('submit', (event) => {
            let erros = [];

            // Verifica se os termos foram aceitos
            if (!checkboxTermos.checked) {
                erros.push('Você precisa aceitar os termos antes de enviar a solicitação.');
            }

            // Verifica se a selfie foi capturada
            if (!selfieDataInput.value.trim()) {
                erros.push('Você precisa capturar uma selfie antes de enviar a solicitação.');
            }

            // Se houver erros, bloqueia o envio e exibe as mensagens
            if (erros.length > 0) {
                event.preventDefault(); // Bloqueia o envio do formulário
                alert(erros.join('\n')); // Mostra as mensagens de erro
            }
        });


    </script>
</body>
</html>
