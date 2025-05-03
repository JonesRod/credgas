<style>
        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); /* Responsivo */
            gap: 10px; /* Espaçamento entre os cards */

        }

        .card {
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 200px; /* Define uma altura mínima */
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .card.status-0 {
            background-color: #ffcc80;
            /* Laranja */
        }

        .card.status-0:hover {
            background-color: #ffb74d;
            /* Laranja mais escuro */
        }

        .card.status-1 {
            background-color: #c8e6c9;
            /* Verde */
        }

        .card.status-1:hover {
            background-color: #a5d6a7;
            /* Verde mais escuro */
        }

        .card.status-2 {
            background-color: #90caf9;
            /* Azul */
        }

        .card.status-2:hover {
            background-color: #64b5f6;
            /* Azul mais escuro */
        }

        .card.status-3 {
            background-color: #ffcdd2;
            /* Vermelho */
        }

        .card.status-3:hover {
            background-color: #ef9a9a;
            /* Vermelho mais escuro */
        }

        .card.status-4 {
            background-color: #ffcdd2;
            /* Vermelho */
        }

        .card.status-4:hover {
            background-color: #ef9a9a;
            /* Vermelho mais escuro */
        }

        .card h2 {
            color: rgb(13, 69, 147);
        }

        .card .valor {
            font-weight: bold;
            color: rgb(13, 69, 147);
        }

        .card img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            vertical-align: middle;
        }

        .btn-voltar {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn-voltar:hover {
            background-color: #0056b3;
        }

        .title {
            text-align: center;
            font-size: 2em;
            margin-bottom: 20px;
            color: #333;
        }

        .filters {
            margin-bottom: 20px;
            text-align: center;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .filters input,
        .filters select {
            padding: 10px;
            margin: 5px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .filters button {
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 5px;
            transition: background-color 0.3s ease;
        }

        .filters button:hover {
            background-color: #0056b3;
        }

        @media (max-width: 600px) {
            .filters {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 10px;
                /* Espaçamento entre os elementos */
            }

            .filters input,
            .filters select,
            .filters button,
            .btn-voltar {
                width: 100%;
                /* Ocupa toda a largura disponível */
                max-width: 600px;
                /* Limita a largura máxima */
                box-sizing: border-box;
                /* Inclui padding e borda no tamanho total */
            }

            .filters form {
                width: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            body {
                font-size: 14px;
            }

            h1,
            h2,
            h3 {
                font-size: 18px;
            }

            .cards-container {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            }

            .card {
                font-size: 14px;
                padding: 10px;
                max-width: 180px;
                min-height: 180px;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }

            .card h2 {
                font-size: 16px;
            }

            .card p {
                font-size: 12px;
            }

            .filters input,
            .filters select,
            .filters button {
                font-size: 14px;
                padding: 8px;
            }

            .btn-voltar {
                font-size: 14px;
                padding: 8px 12px;
            }
        }

        @media (max-width: 380px) {
            .filters {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 10px;
                /* Espaçamento entre os elementos */
            }

            .filters input,
            .filters select,
            .filters button,
            .btn-voltar {
                width: 100%;
                /* Ocupa toda a largura disponível */
                max-width: 300px;
                /* Limita a largura máxima */
                box-sizing: border-box;
                /* Inclui padding e borda no tamanho total */
            }

            .filters form {
                width: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .cards-container {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }

            .card {
                font-size: 12px;
                padding: 8px;
                max-width: 150px;
                min-height: 150px;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }

            .card h2 {
                font-size: 14px;
            }

            .card p {
                font-size: 10px;
            }

            .filters input,
            .filters select,
            .filters button {
                font-size: 12px;
                padding: 6px;
            }

            .btn-voltar {
                font-size: 12px;
                padding: 6px 10px;
            }
        }
    </style>