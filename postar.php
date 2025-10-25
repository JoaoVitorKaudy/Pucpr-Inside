<?php
// Inicia a sessão para verificar se o usuário está logado
session_start();

// Se não houver uma sessão de usuário, redireciona para a página de login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: entrar.html'); 
    exit();
}
?>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUCPR Inside - POSTAR </title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="https://hotmilk.pucpr.br/inovacao-pucpr/wp-content/uploads/2022/09/simbolo-PUCPR-branco-sem-escrito.svg" type="image/x-icon">
</head>
<body>

    <header>
        <nav class="cabecalho">
            <div class="logo_maneira">
                <img src="https://hotmilk.pucpr.br/inovacao-pucpr/wp-content/uploads/2022/09/simbolo-PUCPR-branco-sem-escrito.svg" alt="PUCPR Logo">
                <h2>PUCPR Inside</h2>
            </div>
            <div class="botoes_maneiros">
                <button id="theme-toggle" class="botao">Mudar Tema</button>
                <a href="feed.php" class="botao botao_feed">VOLTAR</a>
                <a href="perfil.php" class="botao botao_perfil">MEU PERFIL</a>
                <a href="sair.php" class="botao botao_sair">SAIR</a>
            </div>
        </nav>
    </header>

    <main>
        <section id="cadastro-container">
            <h2>Crie sua Postagem</h2>
            <p>Compartilhe suas ideias, dúvidas ou novidades com a comunidade!</p>
            
            <form action="processa_postagem.php" method="post" enctype="multipart/form-data" id="form_postagem">
                
                <label for="conteudo">Sua mensagem:</label>
                <textarea name="conteudo" id="conteudo" rows="8" placeholder="O que você está pensando, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>?" required></textarea>

                <label for="midia_post">Anexar imagem ou vídeo (opcional):</label>
                <input type="file" name="midia_post" id="midia_post">

                <button type="submit">Publicar Postagem</button>
            </form>
        </section>
    </main>
    <script src="main.js"></script>
</body>
</html>