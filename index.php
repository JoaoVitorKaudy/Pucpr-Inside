<?php
    // CRIA A CONEXAO COM O BANCO DE DADOS
    // $conn = mysqli_connect("localhost:3306", "root", "PUC@1234", "puc_inside");
    $conn = mysqli_connect("localhost:3307", "root", "", "puc_inside");

// --- A QUERY PARA A PÁGINA INICIAL ---
// É a mesma query do feed, mas com LIMIT 5 para mostrar apenas as 5 mais recentes.
$sql_postagens = "SELECT 
                        p.id, 
                        p.conteudo, 
                        p.data_postagem,
                        u.nome AS autor_nome, 
                        u.foto_perfil AS autor_foto,
                        m.nome_arquivo AS midia_arquivo
                    FROM 
                        postagem AS p
                    JOIN 
                        usuario AS u ON p.id_usuario = u.id
                    LEFT JOIN 
                        midia AS m ON p.id = m.id_postagem
                    ORDER BY 
                        p.data_postagem DESC 
                    LIMIT 5"; // Limita o resultado às 5 postagens mais novas

$resultado_postagens = $conn->query($sql_postagens);

?>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUCPR Inside - INICIO</title>
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
                <a href="entrar.html" class="botao">ENTRAR</a>
                <a href="cadastro.html" class="botao botao_outline">CADASTRAR</a>
            </div>
        </nav>
    </header>

    <main class="feed-container"> 
        
        <h1 class="titulo_principal">Descubra o que está acontecendo!</h1>

        <?php if ($resultado_postagens && $resultado_postagens->num_rows > 0): ?>
            <?php while($post = $resultado_postagens->fetch_assoc()): ?>
                
                <article class="post" id="post-<?php echo $post['id']; ?>">
                    <header id="cabecalho-post">
                        <div class="autor-info">
                            <img src="<?php echo !empty($post['autor_foto']) ? 'uploads/' . htmlspecialchars($post['autor_foto']) : 'imagens/avatar_padrao.png'; ?>" alt="Foto do autor" class="autor-foto">
                            <div>
                                <span class="autor-nome"><?php echo htmlspecialchars($post['autor_nome']); ?></span>
                                <time class="post-data"><?php echo date('d/m/Y \à\s H:i', strtotime($post['data_postagem'])); ?></time>
                            </div>
                        </div>
                    </header>

                    <div class="post-conteudo">
                        <p><?php echo nl2br(htmlspecialchars($post['conteudo'])); ?></p>
                    </div>

                    <?php if (!empty($post['midia_arquivo'])): ?>
                        <div class="post-media">
                            <img src="uploads/<?php echo htmlspecialchars($post['midia_arquivo']); ?>" alt="Mídia da postagem">
                        </div>
                    <?php endif; ?>

                    <section class="comments-section">
                        <h4>Comentários</h4>
                        
                        <?php
                        // --- Query para buscar os comentários desta postagem ---
                        $id_postagem_atual = $post['id'];
                        $sql_comentarios = $conn->prepare("SELECT u.nome AS comentario_autor, c.assunto 
                                                           FROM comentario AS c 
                                                           JOIN usuario AS u ON c.id_usuario = u.id 
                                                           WHERE c.id_postagem = ? 
                                                           ORDER BY c.data_comentario ASC LIMIT 2"); // Mostra só os 2 primeiros comentários
                        $sql_comentarios->bind_param("i", $id_postagem_atual);
                        $sql_comentarios->execute();
                        $resultado_comentarios = $sql_comentarios->get_result();

                        if ($resultado_comentarios->num_rows > 0) {
                            while($comentario = $resultado_comentarios->fetch_assoc()) {
                                echo "<div class='comment'>";
                                echo "<p><strong>" . htmlspecialchars($comentario['comentario_autor']) . ":</strong> " . nl2br(htmlspecialchars($comentario['assunto'])) . "</p>";
                                echo "</div>";
                            }
                        } else {
                            echo "<p class='no-comments'>Nenhum comentário ainda.</p>";
                        }
                        ?>
                        
                        <div class="comment-login-prompt">
                           <p><a href="entrar.html">Entre</a> ou <a href="cadastro.html">cadastre-se</a> para ver mais e deixar um comentário.</p>
                        </div>
                    </section>
                </article>
                <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align: center;">Ainda não há postagens. Volte em breve!</p>
        <?php endif; ?>
        
        <?php $conn->close(); ?>

        <h1 class="titulo_principal">Quer ver mais postagens? Entre ou Cadastre-se e se junte a PUC INSIDE!</h1>

    </main>

    <footer>
        <p>Todos os direitos reservados &copy; 2025</p>
    </footer>

</body>
</html>