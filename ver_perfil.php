<?php
session_start();

// É necessário estar logado para ver perfis
if (!isset($_SESSION['usuario_id'])) {
    header('Location: entrar.html');
    exit();
}

// 1. Pega o ID do usuário que queremos visualizar a partir da URL.
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exit('ID de usuário inválido.');
}
$id_usuario_a_ver = $_GET['id'];

    // CRIA A CONEXAO COM O BANCO DE DADOS
    // $conn = mysqli_connect("localhost:3306", "root", "PUC@1234", "puc_inside");
    $conn = mysqli_connect("localhost:3307", "root", "", "puc_inside");

// 2. Busca os dados do perfil do usuário que estamos visitando.
$stmt_perfil = $conn->prepare("SELECT nome, tipo, curso, foto_perfil, data_cadastro FROM usuario WHERE id = ?");
$stmt_perfil->bind_param("i", $id_usuario_a_ver);
$stmt_perfil->execute();
$resultado_perfil = $stmt_perfil->get_result();

// Se o usuário não for encontrado, mostra uma mensagem de erro.
if ($resultado_perfil->num_rows === 0) {
    exit('Usuário não encontrado.');
}
$usuario = $resultado_perfil->fetch_assoc();
$stmt_perfil->close();

// 3. Busca todas as postagens feitas por esse usuário.
$stmt_postagens = $conn->prepare("SELECT 
                                    p.id, p.conteudo, p.data_postagem, p.qtde_gostei,
                                    m.nome_arquivo AS midia_arquivo
                                 FROM postagem AS p
                                 LEFT JOIN midia AS m ON p.id = m.id_postagem
                                 WHERE p.id_usuario = ?
                                 ORDER BY p.data_postagem DESC");
$stmt_postagens->bind_param("i", $id_usuario_a_ver);
$stmt_postagens->execute();
$resultado_postagens = $stmt_postagens->get_result();

?>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo htmlspecialchars($usuario['nome']); ?></title>
    <link rel="stylesheet" href="style.css">
    </head>
<body>
    <header>
        <nav class="cabecalho">
            <div class="logo_maneira">...</div>
            <div class="botoes_maneiros">
                <a href="feed.php" class="botao botao_feed">FEED</a>
                <a href="perfil.php" class="botao botao_perfil">MEU PERFIL</a>
                <a href="sair.php" class="botao botao_sair">SAIR</a>
            </div>
        </nav>
    </header>

    <main class="feed-container"> <section class="perfil-visitado-info">
            <img src="<?php echo !empty($usuario['foto_perfil']) ? 'uploads/' . htmlspecialchars($usuario['foto_perfil']) : 'imagens/avatar_padrao.png'; ?>" alt="Foto de Perfil">
            <div>
                <h1><?php echo htmlspecialchars($usuario['nome']); ?></h1>
                <p><strong>Tipo:</strong> <?php echo htmlspecialchars($usuario['tipo'] ?? 'Não informado'); ?></p>
                <p><strong>Curso:</strong> <?php echo htmlspecialchars($usuario['curso'] ?? 'Não informado'); ?></p>
                <p><strong>Membro desde:</strong> <?php echo date('d/m/Y', strtotime($usuario['data_cadastro'])); ?></p>
            </div>
        </section>

        <h2 class="titulo-secao-posts">Postagens de <?php echo htmlspecialchars($usuario['nome']); ?></h2>

        <?php if ($resultado_postagens->num_rows > 0): ?>
            <?php while($post = $resultado_postagens->fetch_assoc()): ?>
                <article class="post">
                    <time class="post-data"><?php echo date('d/m/Y \à\s H:i', strtotime($post['data_postagem'])); ?></time>
                    <div class="post-conteudo">
                        <p><?php echo nl2br(htmlspecialchars($post['conteudo'])); ?></p>
                    </div>
                    <?php if (!empty($post['midia_arquivo'])): ?>
                        <div class="post-media">
                            <img src="uploads/<?php echo htmlspecialchars($post['midia_arquivo']); ?>" alt="Mídia da postagem">
                        </div>
                    <?php endif; ?>
                    </article>
            <?php endwhile; ?>
        <?php else: ?>
            <p><?php echo htmlspecialchars($usuario['nome']); ?> ainda não fez nenhuma postagem.</p>
        <?php endif; ?>
        
        <?php
        $stmt_postagens->close();
        $conn->close();
        ?>
    </main>
</body>
</html>