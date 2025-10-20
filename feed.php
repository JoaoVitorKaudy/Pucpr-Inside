<?php
// Inicia a sessão
session_start();

// Se o usuário não estiver logado, redireciona para a página de login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: entrar.html'); 
    exit();
}

    // CRIA A CONEXAO COM O BANCO DE DADOS
    // $conn = mysqli_connect("localhost:3306", "root", "PUC@1234", "puc_inside"); //VERSAO WORKBENCH
    $conn = mysqli_connect("localhost:3307", "root", "", "puc_inside"); //VERSAO XAMPP

// Pega o ID do usuário logado para usar nas queries
$id_usuario_logado = $_SESSION['usuario_id'];

// --- OTIMIZAÇÃO: Busca todos os IDs de usuários que o usuário logado segue ---
// Fazemos isso UMA VEZ aqui para não precisar consultar o banco a cada postagem no loop.
$usuarios_que_sigo = [];
$sql_seguindo = $conn->prepare("SELECT id_seguido FROM seguidor WHERE id_seguidor = ?");
$sql_seguindo->bind_param("i", $id_usuario_logado);
$sql_seguindo->execute();
$resultado_seguindo = $sql_seguindo->get_result();
while ($row = $resultado_seguindo->fetch_assoc()) {
    $usuarios_que_sigo[] = $row['id_seguido'];
}
$sql_seguindo->close();

// --- A QUERY PRINCIPAL E COMPLETA DO FEED ---
$sql_postagens = "SELECT 
                        p.id, 
                        p.conteudo, 
                        p.data_postagem,
                        p.qtde_gostei,
                        u.id AS autor_id, 
                        u.nome AS autor_nome, 
                        u.foto_perfil AS autor_foto,
                        m.nome_arquivo AS midia_arquivo,
                        -- Verifica se o usuário logado já curtiu esta postagem
                        (SELECT COUNT(*) FROM likes WHERE id_postagem = p.id AND id_usuario = ?) > 0 AS usuario_curtiu
                    FROM 
                        postagem AS p
                    JOIN 
                        usuario AS u ON p.id_usuario = u.id
                    LEFT JOIN 
                        midia AS m ON p.id = m.id_postagem
                    ORDER BY 
                        p.data_postagem DESC";

$stmt_postagens = $conn->prepare($sql_postagens);
// Vincula o ID do usuário logado ao placeholder (?) na subquery
$stmt_postagens->bind_param("i", $id_usuario_logado);
$stmt_postagens->execute();
$resultado_postagens = $stmt_postagens->get_result();

?>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUCPR Inside - Feed</title>
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
                <a href="perfil.php" class="botao botao_perfil">PERFIL</a>
                <a href="postar.php" class="botao botao_postar">POSTAR</a>
                <a href="sair.php" class="botao botao_sair">SAIR</a>
            </div>
        </nav>
    </header>

    <main class="feed-container">
        <h1>Últimas Postagens</h1>

        <?php if ($resultado_postagens->num_rows > 0): ?>
            <?php while($post = $resultado_postagens->fetch_assoc()): ?>
                
                <article class="post" id="post-<?php echo $post['id']; ?>">
                    <header class="post-header">
                        <div class="autor-info">
                            <img src="<?php echo !empty($post['autor_foto']) ? 'uploads/' . htmlspecialchars($post['autor_foto']) : 'imagens/avatar_padrao.png'; ?>" alt="Foto do autor" class="autor-foto">
                            <div>
                                <span class="autor-nome"><?php echo htmlspecialchars($post['autor_nome']); ?></span>
                                <time class="post-data"><?php echo date('d/m/Y \à\s H:i', strtotime($post['data_postagem'])); ?></time>
                            </div>
                        </div>

                        <?php
                        // LÓGICA DOS BOTÕES DE AÇÃO (DELETAR ou SEGUIR)
                        if ($post['autor_id'] == $id_usuario_logado):
                        ?>
                            <a href="deletar_postagem.php?id_postagem=<?php echo $post['id']; ?>" class="delete-button" onclick="return confirm('Tem certeza que deseja deletar esta postagem?');">Deletar Post</a>
                        <?php else:
                            if (in_array($post['autor_id'], $usuarios_que_sigo)):
                        ?>
                                <a href="processa_seguir.php?action=deixar_de_seguir&id_seguido=<?php echo $post['autor_id']; ?>&id_postagem=<?php echo $post['id']; ?>" class="follow-button unfollow">Deixar de Seguir</a>
                        <?php else: ?>
                                <a href="processa_seguir.php?action=seguir&id_seguido=<?php echo $post['autor_id']; ?>&id_postagem=<?php echo $post['id']; ?>" class="follow-button">Seguir</a>
                        <?php 
                            endif;
                        endif; 
                        ?>
                    </header>

                    <div class="post-conteudo">
                        <p><?php echo nl2br(htmlspecialchars($post['conteudo'])); ?></p>
                    </div>

                    <?php if (!empty($post['midia_arquivo'])): ?>
                        <div class="post-media">
                            <img src="uploads/<?php echo htmlspecialchars($post['midia_arquivo']); ?>" alt="Mídia da postagem">
                        </div>
                    <?php endif; ?>

                    <footer class="post-footer">
                        <?php
                        // LÓGICA DO BOTÃO GOSTEI/DESCURTIR
                        $like_class = $post['usuario_curtiu'] ? 'liked' : '';
                        $like_text = $post['usuario_curtiu'] ? 'NÃO GOSTEI' : 'GOSTEI';
                        ?>
                        <a href="processa_like.php?id_postagem=<?php echo $post['id']; ?>" class="like-button <?php echo $like_class; ?>"><?php echo $like_text; ?></a>
                        <span class="like-count"><?php echo $post['qtde_gostei']; ?> pessoas gostaram disso</span>
                    </footer>

                    <section class="comments-section">
                        <h4>Comentários</h4>
                        
                        <?php
                        // --- Query para buscar os comentários desta postagem específica ---
                        $id_postagem_atual = $post['id'];
                        $sql_comentarios = $conn->prepare("SELECT c.id, c.assunto, c.data_comentario, u.id as comentario_autor_id, u.nome AS comentario_autor 
                                                           FROM comentario AS c 
                                                           JOIN usuario AS u ON c.id_usuario = u.id 
                                                           WHERE c.id_postagem = ? 
                                                           ORDER BY c.data_comentario ASC");
                        $sql_comentarios->bind_param("i", $id_postagem_atual);
                        $sql_comentarios->execute();
                        $resultado_comentarios = $sql_comentarios->get_result();

                        if ($resultado_comentarios->num_rows > 0) {
                            while($comentario = $resultado_comentarios->fetch_assoc()) {
                                echo "<div class='comment'>";
                                // LÓGICA DO BOTÃO DELETAR COMENTÁRIO
                                if ($comentario['comentario_autor_id'] == $id_usuario_logado) {
                                    echo "<a href='deletar_comentario.php?id_comentario={$comentario['id']}&id_postagem={$id_postagem_atual}' class='delete-button comment-delete' onclick='return confirm(\"Deletar este comentário?\");'>&times;</a>";
                                }
                                echo "<p><strong>" . htmlspecialchars($comentario['comentario_autor']) . ":</strong> " . nl2br(htmlspecialchars($comentario['assunto'])) . "</p>";
                                echo "</div>";
                            }
                        } else {
                            echo "<p class='no-comments'>Ainda não há comentários. Seja o primeiro!</p>";
                        }
                        ?>

                        <form action="processa_comentario.php" method="post" class="comment-form">
                            <input type="hidden" name="id_postagem" value="<?php echo $post['id']; ?>">
                            <textarea name="assunto" placeholder="Escreva seu comentário..." required></textarea>
                            <button type="submit">Comentar</button>
                        </form>
                    </section>
                </article>
                
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center;">Nenhuma postagem encontrada. Que tal criar a primeira?</p>
        <?php endif; ?>
        
        <?php $conn->close(); ?>
    </main>

</body>
</html>