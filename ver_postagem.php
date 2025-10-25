<?php
// Inicia a sessão
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: entrar.html'); 
    exit();
}

// 1. Validar o ID da postagem vindo da URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: feed.php'); // Redireciona se o ID for inválido
    exit();
}
$id_postagem_atual = $_GET['id'];
$id_usuario_logado = $_SESSION['usuario_id'];

// Conexão com o banco
$servidor = "localhost"; $usuario_db = "root"; $senha_db = ""; $banco = "puc_inside";
$conn = mysqli_connect($servidor, $usuario_db, $senha_db, $banco);

// --- OTIMIZAÇÃO: Busca quem o usuário logado segue ---
$usuarios_que_sigo = [];
$sql_seguindo = $conn->prepare("SELECT id_seguido FROM seguidor WHERE id_seguidor = ?");
$sql_seguindo->bind_param("i", $id_usuario_logado);
$sql_seguindo->execute();
$resultado_seguindo = $sql_seguindo->get_result();
while ($row = $resultado_seguindo->fetch_assoc()) {
    $usuarios_que_sigo[] = $row['id_seguido'];
}
$sql_seguindo->close();

// --- QUERY PRINCIPAL (para UMA postagem) ---
$sql_postagem = "SELECT 
                    p.id, p.conteudo, p.data_postagem, p.qtde_gostei,
                    u.id AS autor_id, u.nome AS autor_nome, u.foto_perfil AS autor_foto,
                    m.nome_arquivo AS midia_arquivo,
                    (SELECT COUNT(*) FROM likes WHERE id_postagem = p.id AND id_usuario = ?) > 0 AS usuario_curtiu
                FROM postagem AS p
                JOIN usuario AS u ON p.id_usuario = u.id
                LEFT JOIN midia AS m ON p.id = m.id_postagem
                WHERE p.id = ?"; // Filtro para pegar SÓ a postagem com o ID da URL

$stmt_postagem = $conn->prepare($sql_postagem);
$stmt_postagem->bind_param("ii", $id_usuario_logado, $id_postagem_atual);
$stmt_postagem->execute();
$resultado_postagem = $stmt_postagem->get_result();

if ($resultado_postagem->num_rows == 0) {
    exit("Postagem não encontrada."); // Mensagem de erro se o post não existir
}
$post = $resultado_postagem->fetch_assoc();

?>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postagem de <?php echo htmlspecialchars($post['autor_nome']); ?></title>
    <link rel="stylesheet" href="style.css">
    <!--ICONE-->
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
                <a href="feed.php" class="botao botao_feed">VOLTAR AO FEED</a>
                <a href="perfil.php" class="botao botao_perfil">PERFIL</a>
                <a href="sair.php" class="botao botao_sair">SAIR</a>
            </div>
        </nav>
    </header>

    <main class="feed-container">
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
                // Lógica dos botões de ação (DELETAR ou SEGUIR)
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
                // Lógica do botão GOSTEI/DESCURTIR
                $like_class = $post['usuario_curtiu'] ? 'liked' : '';
                $like_text = $post['usuario_curtiu'] ? 'Descurtir' : 'Gostei';
                ?>
                <a href="processa_like.php?id_postagem=<?php echo $post['id']; ?>" class="like-button <?php echo $like_class; ?>"><?php echo $like_text; ?></a>
                <span class="like-count"><?php echo $post['qtde_gostei']; ?> pessoas gostaram disso</span>
            </footer>

            <section class="comments-section">
                <h4>Comentários</h4>
                
                <?php
                // --- Query para buscar TODOS os comentários ---
                $sql_comentarios = $conn->prepare("SELECT c.id, c.assunto, c.data_comentario, u.id as comentario_autor_id, u.nome AS comentario_autor 
                                                   FROM comentario AS c 
                                                   JOIN usuario AS u ON c.id_usuario = u.id 
                                                   WHERE c.id_postagem = ? 
                                                   ORDER BY c.data_comentario ASC"); // Sem LIMIT
                $sql_comentarios->bind_param("i", $id_postagem_atual);
                $sql_comentarios->execute();
                $resultado_comentarios = $sql_comentarios->get_result();

                if ($resultado_comentarios->num_rows > 0) {
                    while($comentario = $resultado_comentarios->fetch_assoc()) {
                        echo "<div class='comment'>";
                        // Lógica do botão DELETAR COMENTÁRIO
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
        
        <?php $conn->close(); ?>
    </main>
    <script src="main.js"></script>
</body>
</html>