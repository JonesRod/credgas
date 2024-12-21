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

// Consulta para verificar se o cliente já possui crediário e buscar seus detalhes
$sql_query = $mysqli->prepare("SELECT * FROM meus_clientes WHERE id = ?");
$sql_query->bind_param('i', $id);
$sql_query->execute();
$result = $sql_query->get_result();
$crediario = $result->fetch_assoc();

$frente = htmlspecialchars($crediario['img_frente']); 
$verso = htmlspecialchars($crediario['img_verso']);
$self = htmlspecialchars($crediario['img_self']);

if ($frente !=''){
    // Se existe e não está vazio, atribui o valor à variável logo
    $frente = "arquivos/".htmlspecialchars($crediario['img_frente']);
    //echo ('oii1').$frente;
} else {
    // Se não existe ou está vazio, define um valor padrão
    $frente = 'arquivos/9734564-default-avatar-profile-icon-of-social-media-user-vetor.jpg';
    //echo ('oii2').$frente;
}

if ($verso !=''){
    // Se existe e não está vazio, atribui o valor à variável logo
    $verso = "arquivos/".htmlspecialchars($crediario['img_verso']);
    //echo ('oii1').$frente;
} else {
    // Se não existe ou está vazio, define um valor padrão
    $verso = 'arquivos/9734564-default-avatar-profile-icon-of-social-media-user-vetor.jpg';
    //echo ('oii2').$frente;
}
if ($self !=''){
    // Se existe e não está vazio, atribui o valor à variável logo
    $self = "arquivos/".htmlspecialchars($crediario['img_self']);
    //echo ('oii1').$frente;
} else {
    // Se não existe ou está vazio, define um valor padrão
    $self = 'arquivos/9734564-default-avatar-profile-icon-of-social-media-user-vetor.jpg';
    //echo ('oii2').$frente;
}


?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crediário</title>
    <link rel="stylesheet" href="../../styles.css">
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

        input[type="text"], input[type="file"], button {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
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

        .video-container, .canvas-container, .preview-container {
            text-align: center;
            margin: 20px 0;
        }

        video, canvas, img {
            width: 100%;
            max-width: 300px;
            border: 2px solid #ccc;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        @media (max-width: 480px) {
            .form-actions {
                flex-direction: column;
            }

            button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Crediário</h1>


            <h2>Detalhes do Crediário</h2>
            <p><strong>Nome:</strong> <?php echo htmlspecialchars($crediario['nome_completo']); ?></p>
            <p><strong>Data de Nascimento:</strong> <?php echo date('d/m/Y', strtotime($crediario['nascimento'])); ?></p>
            <p><strong>CPF:</strong> <?php echo htmlspecialchars($crediario['cpf']); ?></p>
            <p><strong>Celular:</strong> <?php echo htmlspecialchars($crediario['celular1']); ?></p>
            <a href="perfil_cliente.php" class="btn">Editar Dados</a>
      
            <form action="processa_crediario.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" required value="<?php echo $id; ?>">
                <div class="form-group">
                    <label for="cep">CEP:</label>
                    <input type="text" id="cep" name="cep" required value="<?php echo htmlspecialchars($crediario['cep']); ?>">
                </div>

                <div class="form-group">
                    <label for="uf">Estado:</label>
                    <input type="text" id="uf" name="uf" required value="<?php echo htmlspecialchars($crediario['uf']); ?>">
                </div>

                <div class="form-group">
                    <label for="cidade">Cidade:</label>
                    <input type="text" id="cidade" name="cidade" required value="<?php echo htmlspecialchars($crediario['cidade']); ?>">
                </div>

                <div class="form-group">
                    <label for="endereco">Endereço:</label>
                    <input type="text" id="endereco" name="endereco" required value="<?php echo htmlspecialchars($crediario['endereco']); ?>">
                </div>

                <div class="form-group">
                    <label for="numero">Número:</label>
                    <input type="text" id="numero" name="numero" required value="<?php echo htmlspecialchars($crediario['numero']); ?>">
                </div>

                <div class="form-group">
                    <label for="bairro">Bairro:</label>
                    <input type="text" id="bairro" name="bairro" required value="<?php echo htmlspecialchars($crediario['bairro']); ?>">
                </div>

                <div class="form-group">
                    <label for="documento_foto_frente">Documento com Foto (Frente):</label>
                    <input type="file" id="documento_foto_frente" name="documento_foto_frente" accept="image/*,application/pdf">
                    <img id="preview_frente" alt="Pré-visualização Frente" src="<?php echo $frente;?>">
                </div>

                <div class="form-group">
                    <label for="documento_foto_verso">Documento com Foto (Verso):</label>
                    <input type="file" id="documento_foto_verso" name="documento_foto_verso" accept="image/*,application/pdf" required>
                    <img id="preview_verso" alt="Pré-visualização Verso" src="<?php echo $verso;?>">
                </div>

                <h2>Selfie de Perfil</h2>
                <div class="video-container">
                    <?php if ($self!=''):?>
                        <img id="preview_self" alt="Pré-visualização self" src="<?php echo $self;?>">
                    <?php else:?>
                        <video id="camera" autoplay></video>
                        <button type="button" id="start-camera">Iniciar Câmera</button>
                        <button type="button" id="capture" style="display: none;">Capturar</button>
                    <?php endif; ?>
                </div>

                <div class="canvas-container" style="display: none;">
                    <canvas id="snapshot"></canvas>
                    <button type="button" id="retake">Tirar Outra</button>
                </div>

                <input type="hidden" name="selfie_data" id="selfie_data">

                <div class="form-actions">
                    <button type="submit" class="btn">Enviar Solicitação</button>
                    <a href="perfil_cliente.php" class="btn cancel">Cancelar</a>
                </div>
            </form>

    </div>

    <script>
        const video = document.getElementById('camera');
        const startCameraButton = document.getElementById('start-camera');
        const captureButton = document.getElementById('capture');
        const retakeButton = document.getElementById('retake');
        const canvas = document.getElementById('snapshot');
        const selfieDataInput = document.getElementById('selfie_data');
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
       /* documentoFrente.addEventListener('change', (event) => {
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
        });*/


    </script>
</body>
</html>
