def calcular_saldo(transacoes):
    saldo = 0

    # Itera sobre cada transação e atualiza o saldo
    for transacao in transacoes:
        saldo += transacao

    # Retorna o saldo formatado em moeda brasileira com duas casas decimais
    return f"Saldo: R$ {saldo:.2f}"


# Leitura da entrada do usuário
entrada_usuario = input()

# Processa a entrada removendo colchetes e espaços extras
entrada_usuario = entrada_usuario.strip("[]").replace(" ", "")

# Converte a string de entrada em uma lista de floats
transacoes = [float(valor) for valor in entrada_usuario.split(",") if valor]

# Calcula o saldo com base nas transações informadas
resultado = calcular_saldo(transacoes)

# Imprime o resultado formatado
print(resultado)
