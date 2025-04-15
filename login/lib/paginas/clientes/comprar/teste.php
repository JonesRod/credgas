function validarEntradaSaldo() {
                            const inputSaldo = document.getElementById('input_saldo');
                            const valorSaldo = parseFloat(inputSaldo.value.replace(/\./g, '').replace(',', '.')) || 0;
                            const totalComFrete = parseFloat("<?php echo $totalComFrete; ?>");

                            if (valorSaldo <= 0 || valorSaldo > totalComFrete) {
                                alert('O valor do saldo deve ser maior que 0 e menor ou igual ao valor total da compra.');
                                inputSaldo.value = ''; // Limpa o campo
                                inputSaldo.focus(); // Foca no campo para correção
                                return false; // Impede o envio do formulário
                            }
                            return true; // Permite o envio do formulário
                        }

                        document.querySelector('button[type="submit"]').addEventListener('click', function(event) {
                            if (!validarEntradaSaldo()) {
                                event.preventDefault(); // Impede o envio do formulário se a validação falhar
                            }
                        });