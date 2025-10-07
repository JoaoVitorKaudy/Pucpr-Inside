<?php
// Inicia a sessão para podermos usar as variáveis de sessão
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: entrar.html'); 
    exit();
}

?>
<html lang="pt-BR">

<!--CABEÇA-->
<head>

    <meta charset="UTF-8">
    <title>PUCPR Inside - PERFIL</title>

    <!--CSS-->
    <link rel="stylesheet" href="style.css">

    <!--FONTE DO GOOGLE-->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    
    <!--ICONE-->
    <link rel="shortcut icon" href="https://hotmilk.pucpr.br/inovacao-pucpr/wp-content/uploads/2022/09/simbolo-PUCPR-branco-sem-escrito.svg" type="image/x-icon">

</head>

<!--CORPO-->
<body>

    <!--CABECALHO-->
    <header>

        <nav class="cabecalho">

            <!--LOGO-->
            <div class="logo_maneira">
                <img src="https://hotmilk.pucpr.br/inovacao-pucpr/wp-content/uploads/2022/09/simbolo-PUCPR-branco-sem-escrito.svg" alt="PUCPR Logo">
                <h2>PUCPR Inside</h2>
            </div>

            <!--BOTOES DE ENTRAR E CADASTRAR-->
            <div class="botoes_maneiros">
                <a href="entrar.html" class="botao">Entrar</a>
                <a href="cadastro.html" class="botao botao_outline">Cadastrar</a>
                <a href="index.html" class="botao">voltar</a>
                <a href="#" class="botao botao_especial">POSTAR</a>
            </div>

        </nav>

    </header>

    <!--CONTEUDO-->
    <main>

        <section id="cadastro-container">
            <h1>Bem-vindo ao seu painel, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>!</h1>
            <p>Seu login foi um sucesso e você está em uma área restrita.</p>
        </section>

    </main>

</body>

</html>