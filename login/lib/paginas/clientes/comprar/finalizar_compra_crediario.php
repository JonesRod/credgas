<?php
session_start();
include('../../../conexao.php'); // Conexão com o banco

// Configurar cabeçalhos para JSON
header('Content-Type: application/json; charset=utf-8');

// Verificação de sessão
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Sessão expirada.']);
    exit;
}

$id_session = $_SESSION['id'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Receber os dados JSON
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (isset($data['senha_cliente']) && isset($data['senha_compra'])) {
            // Buscar a senha do cliente no banco
            $bd_cliente = $mysqli->query("SELECT senha_login FROM meus_clientes WHERE id = $id_session");
            if (!$bd_cliente) {
                throw new Exception('Erro ao buscar dados do cliente.');
            }
            $dados = $bd_cliente->fetch_assoc();
            $senha_compra = $dados['senha_login'];

            // Verificar e sanitizar os dados recebidos
            $tipo_compra = 'crediario';
            $id_cliente = isset($data['id_cliente']) ? intval($data['id_cliente']) : 0;
            $id_parceiro = isset($data['id_parceiro']) ? intval($data['id_parceiro']) : 0;
            $valor_frete = isset($data['valor_frete']) ? floatval($data['valor_frete']) : 0.0;
            $valor_total_sem_crediario = isset($data['valor_total_sem_crediario']) ? floatval($data['valor_total_sem_crediario']) : 0.0;
            $valor_total_crediario = isset($data['valor_total_crediario']) ? floatval($data['valor_total_crediario']) : 0.0;
            $detalhes_produtos = isset($data['detalhes_produtos']) ? $data['detalhes_produtos'] : '';
            $entrega = isset($data['entrega']) ? $data['entrega'] : '';
            $rua = isset($data['rua']) ? $data['rua'] : '';
            $bairro = isset($data['bairro']) ? $data['bairro'] : '';
            $numero = isset($data['numero']) ? $data['numero'] : '';
            $contato = isset($data['contato']) ? $data['contato'] : '';
            $entrada = isset($data['entrada']) ? floatval($data['entrada']) : 0.0;
            $restante = isset($data['restante']) ? floatval($data['restante']) : 0.0;
            $tipo_entrada_crediario = isset($data['tipo_entrada_crediario']) ? $data['tipo_entrada_crediario'] : '';
            if ($tipo_entrada_crediario === '1') {
                $tipo_entrada_crediario = 'pix';
            } else if ($tipo_entrada_crediario === '2') {
                $tipo_entrada_crediario = 'credito';
            } else {
                $tipo_entrada_crediario = 'debito';
            }
            $tipo_cartao = $tipo_entrada_crediario;
            $bandeiras_aceitas = isset($data['bandeiras_aceitas']) ? $data['bandeiras_aceitas'] : '';
            $comentario = isset($data['comentario']) ? $data['comentario'] : '';
            $parcelas = isset($data['parcelas']) ? intval($data['parcelas']) : 1;
            $valor_parcela = isset($data['valor_parcela']) ? floatval($data['valor_parcela']) : 0.0;
            $senha_cliente = $data['senha_cliente'];
            
            $data_hora = isset($data['data_hora']) ? $data['data_hora'] : '';
            $tota_compra = $valor_total_crediario + $valor_frete + $valor_total_sem_crediario;

            // Verifique se o valor foi calculado corretamente
            if ($tota_compra === null || $tota_compra === 0) {
                echo json_encode(['success' => false, 'message' => 'Erro: O valor total da compra não foi calculado corretamente.']);
                exit;
            }

            $num_cartao = isset($data['num_cartao']) ? $data['num_cartao'] : '';
            $nome_cartao = isset($data['nome_cartao']) ? $data['nome_cartao'] : '';
            $validade = isset($data['validade']) ? $data['validade'] : '';
            $cod_seguranca = isset($data['cod_seguranca']) ? $data['cod_seguranca'] : '';
            $qt_parcelas_entrada = isset($data['qt_parcelas_entrada']) ? intval($data['qt_parcelas_entrada']) : 1;
            $valorParcela_entrada = isset($data['valorParcela_entrada']) ? floatval($data['valorParcela_entrada']) : 0.0;
            $salvar_cartao = isset($data['salvar_cartao']) ? intval($data['salvar_cartao']) : 0;

            $status_cliente = 0; // Status do cliente
            $status_parceiro = 0; // Status do parceiro
            
            // Verifica se a senha foi preenchida
            if (empty($senha_cliente)) {
                echo json_encode(['success' => false, 'message' => 'Senha não informada.']);
                exit;
            }

            // Verifica se a senha está correta usando password_verify
            if (!password_verify($senha_cliente, $senha_compra)) {
                echo json_encode(['success' => false, 'message' => 'Senha incorreta.']);
                exit;
            }

            // Processar a compra (exemplo de inserção no banco de dados)
            $stmt = $mysqli->prepare("INSERT INTO pedidos (
                data,
                id_cliente, 
                id_parceiro, 
                produtos, 
                valor_frete,
                valor,
                formato_compra,
                entrada,
                forma_pg_entrada,
                qt_parcela_entrada,
                valor_parcela_entrada,
                valor_restante,
                forma_pg_restante,
                qt_parcelas,
                valor_parcela,
                tipo_entrega,
                endereco_entrega,
                num_entrega,
                bairro_entrega,
                contato_recebedor,
                comentario,
                status_cliente,
                status_parceiro) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            if (!$stmt) {
                throw new Exception('Erro ao preparar a consulta: ' . $mysqli->error);
            }

            $stmt->bind_param(
                "siissssssisssssssssssii", // Tipos de dados: s = string, i = inteiro, d = double
                $data_hora,         // s: data
                $id_cliente,        // i: id_cliente
                $id_parceiro,       // i: id_parceiro
                $detalhes_produtos, // s: produtos
                $valor_frete,       // d: valor_frete
                $tota_compra,       // d: valor
                $tipo_compra,       // s: tipo_compra
                $entrada,           // d: entrada
                $tipo_entrada_crediario, // s: forma_pg_entrada
                $qt_parcelas_entrada, // i: qt_parcelas_entrada
                $valorParcela_entrada, // d: valor_parcela_entrada
                $restante,          // d: valor_restante
                $tipo_compra,       // s: forma_pg_restante
                $parcelas,          // i: qt_parcelas
                $valor_parcela,     // d: valor_parcela
                $entrega,           // s: tipo_entrega
                $rua,               // s: endereco_entrega
                $numero,            // s: num_entrega
                $bairro,            // s: bairro_entrega
                $contato,           // s: contato_recebedor
                $comentario,        // s: comentario
                $status_cliente,    // i: status_cliente
                $status_parceiro    // i: status_parceiro
            );

            if ($stmt->execute()) {
                $num_pedido = $stmt->insert_id; // Obter o ID do pedido inserido
                $stmt->close();

                // Buscar cartões do cliente usando prepared statements
                $stmt = $mysqli->prepare("SELECT * FROM cartoes_clientes WHERE id_cliente = ?");
                if ($stmt) {
                    $stmt->bind_param("i", $id_cliente);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    $cartoes = array();
                    $cartoes_credito = 0;
                    $cartoes_debito = 0;
                    while ($row = $result->fetch_assoc()) {
                        $cartoes[] = $row;
                        if ($row['tipo'] === 'credito') {
                            $cartoes_credito++;
                        } elseif ($row['tipo'] === 'debito') {
                            $cartoes_debito++;
                        }
                    }

                    $stmt->close();
                } else {
                    die("Erro na preparação da consulta: " . $mysqli->error);
                }

                // salvar o cartão de crédito ou débito se necessário
                if ($salvar_cartao == 1) {
                    // Criptografar o código de segurança
                    $cod_seguranca_criptografado = password_hash($cod_seguranca, PASSWORD_DEFAULT);
        
                    // Verificar se o cartão já está cadastrado
                    $stmt = $mysqli->prepare("SELECT id FROM cartoes_clientes WHERE id_cliente = ? AND num_cartao = ? AND tipo = ?");

                    if ($stmt) {
                        $stmt->bind_param("iss", $id_cliente, $num_cartao, $tipo_cartao);
                        $stmt->execute();
                        $stmt->store_result();
        
                        if ($stmt->num_rows > 0) {
                            $stmt->close();
                            //$mensagem_erro = "Este cartão já está cadastrado.";
                        } else {
                            $stmt->close();
        
                            // Verificar se o limite de cartões foi atingido
                            if (($tipo_cartao === 'credito' && $cartoes_credito >= 5) || ($tipo_cartao === 'debito' && $cartoes_debito >= 5)) {
                                //$mensagem_erro = "Você atingiu o limite de 5 cartões de $tipo_cartao.";
                            } else {
                                // Salvar o novo cartão no banco de dados
                                $stmt = $mysqli->prepare("INSERT INTO cartoes_clientes (id_cliente, num_cartao, validade, cod_seguranca, tipo, nome) VALUES (?, ?, ?, ?, ?, ?)");
                                if ($stmt) {
                                    $stmt->bind_param("isssss", $id_cliente, $num_cartao, $validade, $cod_seguranca_criptografado, $tipo_cartao, $nome_cartao);
                                    $stmt->execute();
                                    $stmt->close();
                                } else {
                                    die("Erro ao salvar o cartão: " . $mysqli->error);
                                }
                            }
                        }
                    } else {
                        die("Erro na preparação da consulta: " . $mysqli->error);
                    }
                }

                // Salvar notificação na tabela contador_notificacoes_cliente
                $msg = "Pedido #$num_pedido em Análise.";
                $stmt_notificacao = $mysqli->prepare("INSERT INTO contador_notificacoes_cliente (data, id_cliente, msg, referente, lida) VALUES (?, ?, ?, 'pedido', 1)");
                if ($stmt_notificacao) {
                    $stmt_notificacao->bind_param("sis", $data_hora, $id_cliente, $msg);
                    $stmt_notificacao->execute();
                    $stmt_notificacao->close();
                } else {
                    throw new Exception("Erro ao salvar a notificação: " . $mysqli->error);
                }

                // Excluir o pedido do carrinho
                $stmt_carrinho = $mysqli->prepare("DELETE FROM carrinho WHERE id_cliente = ? AND id_parceiro = ?");
                if ($stmt_carrinho) {
                    $stmt_carrinho->bind_param("ii", $id_cliente, $id_parceiro);
                    $stmt_carrinho->execute();
                    $stmt_carrinho->close();
                } else {
                    throw new Exception("Erro ao excluir do carrinho: " . $mysqli->error);
                }

                echo json_encode(['success' => true, 'message' => 'Compra finalizada com sucesso.']);
            } else {
                throw new Exception('Erro ao executar a consulta: ' . $stmt->error);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
