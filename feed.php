<?php

session_start();

// Se o usuário não estiver logado, redireciona para a página de login
if (!isset($_SESSION['usuario_id'])) { header('Location: entrar.html'); exit(); }

    // CRIA A CONEXAO COM O BANCO DE DADOS
    // $conn = mysqli_connect("localhost:3306", "root", "PUC@1234", "puc_inside");
    $conn = mysqli_connect("localhost:3307", "root", "", "puc_inside");

$sql_postagens = "SELECT 
                        p.id, 
                        SUBSTRING(p.conteudo, 1, 200) AS conteudo_previo, 
                        LENGTH(p.conteudo) > 200 AS conteudo_longo, 
                        p.data_postagem,
                        u.nome AS autor_nome, 
                        u.foto_perfil AS autor_foto,
                        m.nome_arquivo AS midia_arquivo,
                        (SELECT COUNT(*) FROM comentario WHERE id_postagem = p.id) AS total_comentarios
                    FROM 
                        postagem AS p
                    JOIN 
                        usuario AS u ON p.id_usuario = u.id
                    LEFT JOIN 
                        midia AS m ON p.id = m.id_postagem
                    ORDER BY 
                        p.data_postagem DESC";

$resultado_postagens = $conn->query($sql_postagens);

?>
<html lang="pt-BR">

<!--CABEÇA-->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUCPR Inside - Feed</title>
    
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

            <!--BOTOES-->
            <div class="botoes_maneiros">
                <a href="perfil.php" class="botao botao_perfil">PERFIL</a>
                <a href="postar.php" class="botao botao_postar">POSTAR</a>
                <a href="sair.php" class="botao botao_sair">SAIR</a>
                <button id="theme-toggle" class="botao">Mudar Tema</button>
            </div>

        </nav>
    </header>

    <!--mostra todas as postagens-->
    <main class="feed-container">
        <h1>Últimas Postagens</h1>

        <!--confere se tem ao menos uma postagem e mostra ela-->
        <?php if ($resultado_postagens->num_rows > 0): ?>
            <?php while($post = $resultado_postagens->fetch_assoc()): ?>
                
                <a href="ver_postagem.php?id=<?php echo $post['id']; ?>" class="post-preview-link">
                    <article class="post" id="post-<?php echo $post['id']; ?>">
                        <header class="post-header">
                            <div class="autor-info">
                                <img src="<?php echo !empty($post['autor_foto']) ? 'uploads/' . htmlspecialchars($post['autor_foto']) : 'imagens/avatar_padrao.png'; ?>" alt="Foto do autor" class="autor-foto">
                                <div>
                                    <span class="autor-nome"><?php echo htmlspecialchars($post['autor_nome']); ?></span>
                                    <time class="post-data"><?php echo date('d/m/Y \à\s H:i', strtotime($post['data_postagem'])); ?></time>
                                </div>
                            </div>
                        </header>

                        <div class="post-conteudo">
                            <p><?php echo nl2br(htmlspecialchars($post['conteudo_previo'])); ?>
                               <?php if ($post['conteudo_longo']): // Se o texto original for maior... ?>
                                   <span class="ler-mais">... (Ver postagem completa)</span>
                               <?php endif; ?>
                            </p>
                        </div>

                        <?php if (!empty($post['midia_arquivo'])): ?>
                            <div class="post-media">
                                <img src="uploads/<?php echo htmlspecialchars($post['midia_arquivo']); ?>" alt="Mídia da postagem">
                            </div>
                        <?php endif; ?>

                        <section class="comments-section-preview">
                            <h4>Comentários (<?php echo $post['total_comentarios']; ?>)</h4>
                            
                            <?php
                            //query para buscar os 3 primeiros comentários
                            $id_postagem_atual = $post['id'];
                            $sql_comentarios = $conn->prepare("SELECT u.nome AS comentario_autor, c.assunto
                                                               FROM comentario AS c 
                                                               JOIN usuario AS u ON c.id_usuario = u.id 
                                                               WHERE c.id_postagem = ? 
                                                               ORDER BY c.data_comentario ASC LIMIT 3"); // LIMIT 3
                            $sql_comentarios->bind_param("i", $id_postagem_atual);
                            $sql_comentarios->execute();
                            $resultado_comentarios = $sql_comentarios->get_result();

                            if ($resultado_comentarios->num_rows > 0) {
                                while($comentario = $resultado_comentarios->fetch_assoc()) {
                                    echo "<div class='comment-preview'>";
                                    echo "<p><strong>" . htmlspecialchars($comentario['comentario_autor']) . ":</strong> " . nl2br(htmlspecialchars($comentario['assunto'])) . "</p>";
                                    echo "</div>";
                                }
                                if ($post['total_comentarios'] > 3) {
                                    echo "<p class='ver-mais-comentarios'>Ver todos os {$post['total_comentarios']} comentários...</p>";
                                }
                            } else {
                                echo "<p class='no-comments'>Nenhum comentário.</p>";
                            }
                            ?>
                        </section>
                    </article>
                </a>
                
            <?php endwhile; ?>

        <!--se nao tiver nenhuma postagem-->    
        <?php else: ?>
            <p style="text-align:center;">Nenhuma postagem encontrada. Que tal criar a primeira?</p>
        <?php endif; ?>
        
        <?php $conn->close(); ?>
    </main>

    <!--JS-->
    <script src="main.js"></script>
</body>
</html>