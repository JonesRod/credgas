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
$sql_query = $mysqli->prepare("SELECT nome_completo, cpf, nascimento, celular1, email, cep, uf, cidade, endereco, numero, bairro, status_crediario FROM meus_clientes WHERE id = ?");
$sql_query->bind_param('i', $id);
$sql_query->execute();
$result = $sql_query->get_result();
$crediario = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crediário</title>
    <link rel="stylesheet" href="../../styles.css">
    <style>
        .video-container, .canvas-container {
            display: block;
            text-align: center;
            margin-top: 20px;
        }
        .canvas-container {
            display: none;
        }
        video, canvas {
            width: 100%;
            max-width: 300px;
            border: 2px solid #ccc;
        }
        button {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Crediário</h1>

    <?php if ($crediario && $crediario['status_crediario']!='INATIVO'): ?>
        <div class="crediario-detalhes">
            <h2>Detalhes do Crediário</h2>
            <p><strong>Nome:</strong> <?php echo htmlspecialchars($crediario['nome_completo']); ?></p>
            <p><strong>Data de Nascimento:</strong> <?php echo date('d/m/Y', strtotime($crediario['nascimento'])); ?></p>
            <p><strong>CPF:</strong> <?php echo htmlspecialchars($crediario['cpf']); ?></p>
            <p><strong>Celular:</strong> <?php echo htmlspecialchars($crediario['celular1']); ?></p>
            <p><strong>E-mail:</strong> <?php echo htmlspecialchars($crediario['email']); ?></p>
            <p><strong>CEP:</strong> <?php echo htmlspecialchars($crediario['cep']); ?></p>
            <p><strong>Estado:</strong> <?php echo htmlspecialchars($crediario['uf']); ?></p>
            <p><strong>Cidade:</strong> <?php echo htmlspecialchars($crediario['cidade']); ?></p>
            <p><strong>Rua/AV:</strong> <?php echo htmlspecialchars($crediario['endereco']); ?></p>
            <p><strong>Nº:</strong> <?php echo htmlspecialchars($crediario['numero']); ?></p>
            <p><strong>Bairro:</strong> <?php echo htmlspecialchars($crediario['bairro']); ?></p>
            <a href="editar_crediario.php" class="btn">Editar Dados</a>
        </div>
    <?php else: ?>
        <div class="solicitar-crediario">
            <h2>Solicitar Crediário</h2>
            <form action="processa_crediario.php" method="POST" enctype="multipart/form-data">
                <label for="cep">CEP:</label>
                <input type="text" id="cep" name="cep" required 
                value="<?php echo htmlspecialchars($crediario['cep']); ?>">

                <label for="uf">Estado:</label>
                <input type="text" id="uf" name="uf" required
                value="<?php echo htmlspecialchars($crediario['uf']); ?>">

                <label for="cidade">Cidade:</label>
                <input type="text" id="cidade" name="cidade" required
                value="<?php echo htmlspecialchars($crediario['cidade']); ?>">

                <label for="endereco">Endereço Completo:</label>
                <input id="endereco" name="endereco" required
                value="<?php echo htmlspecialchars($crediario['endereco']); ?>">

                <label for="numero">Nº:</label>
                <input type="text" id="numero" name="numero" required
                value="<?php echo htmlspecialchars($crediario['numero']); ?>">

                <label for="Bairro">Bairro:</label>
                <input id="bairro" name="bairro" required
                value="<?php echo htmlspecialchars($crediario['bairro']); ?>">

                <label for="documento_foto_frente">Documento com Foto-frente:</label>
                <input type="file" id="documento_foto_frente" name="documento_foto_frente" accept="image/*,application/pdf" required>

                <label for="documento_foto_verso">Documento com Foto-verso:</label>
                <input type="file" id="documento_foto_verso" name="documento_foto_verso" accept="image/*,application/pdf" required>

                <h1>Selfie de Perfil</h1>
                <div class="video-container">
                    <video id="camera" autoplay></video>
                    <button id="start-camera">Iniciar Câmera</button>
                    <button id="capture" style="display: none;">Capturar</button>
                </div>

                <div class="canvas-container">
                    <canvas id="snapshot"></canvas>
                    <button id="save" style="display: none;">Salvar</button>
                    <button id="retake" style="display: none;">Tirar Outra</button>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">Enviar Solicitação</button>
                    <a href="perfil_cliente.php" class="btn cancel">Cancelar</a>
                </div>
            </form>
        </div>
    <?php endif; ?>
</body>

<script>
    const video = document.getElementById('camera');
    const startCameraButton = document.getElementById('start-camera');
    const captureButton = document.getElementById('capture');
    const saveButton = document.getElementById('save');
    const retakeButton = document.getElementById('retake');
    const videoContainer = document.querySelector('.video-container');
    const canvas = document.getElementById('snapshot');
    const canvasContainer = document.querySelector('.canvas-container');
    let stream = null;

    // Ligar a câmera somente quando clicar no botão "Iniciar Câmera"
    startCameraButton.addEventListener('click', () => {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
            .then((mediaStream) => {
                stream = mediaStream;
                video.srcObject = stream;
                startCameraButton.style.display = "none";
                captureButton.style.display = "inline-block";
            })
            .catch((error) => {
                alert("Não foi possível acessar a câmera. Verifique as permissões ou tente outro dispositivo.");
                console.error("Erro ao acessar câmera:", error);
            });
    });

    // Captura a imagem do vídeo
    captureButton.addEventListener('click', () => {
        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        videoContainer.style.display = "none";
        canvasContainer.style.display = "block";
        saveButton.style.display = "inline-block";
        retakeButton.style.display = "inline-block";

        // Parar a câmera após capturar a imagem
        if (stream) {
            stream.getTracks().forEach((track) => track.stop());
            stream = null;
        }
    });

    // Tirar outra selfie
    retakeButton.addEventListener('click', () => {
        canvasContainer.style.display = "none";
        videoContainer.style.display = "block";
        startCameraButton.style.display = "inline-block";
        captureButton.style.display = "none";
        saveButton.style.display = "none";
        retakeButton.style.display = "none";
    });

    // Salvar a imagem capturada
    saveButton.addEventListener('click', () => {
        const dataURL = canvas.toDataURL("image/png");
        const link = document.createElement('a');
        link.href = dataURL;
        link.download = 'selfie.png';
        link.click();
    });
</script>
</html>
