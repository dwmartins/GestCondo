<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro interno</title>
    <style>
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        .error-page {
            padding: 0 20px;
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .error-page img {
            width: 100%;
            max-width: 500px;
            margin-bottom: 20px;
        }

        .error-page h1 {
            color:#6c757d; 
            margin-bottom: 10px;
        }

        .error-page p {
            font-size: 22px;
            color:#6c757d;
            margin: 0;
        }
        
    </style>
</head>
<body>
    <section class="error-page">
        <img src="{{ asset('assets/svg/error.svg') }}" alt="Error">
        <h1>Ops, ocorreu um erro.</h1>
        <p>Já estamos trabalhando nisso!</p>
    </section>
</body>
</html>