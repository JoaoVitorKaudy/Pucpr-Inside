<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: entrar.html'); exit(); }
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { exit('Usuário inválido.'); }

$id_usuario_lista = $_GET['id'];

$servidor = "localhost"; $usuario_db = "root"; $senha_db = ""; $banco = "puc_inside";
$conn = mysqli_connect($servidor, $usuario_db, $senha_db, $banco);

// Busca o nome do usuário dono da lista
$stmt_nome = $conn->prepare("SELECT nome FROM usuario WHERE id = ?");
$stmt_nome->bind_param("i", $id_usuario_lista);
$stmt_nome->execute();
$nome_usuario = $stmt_nome->get_result()->fetch_assoc()['nome'];

// Busca a lista de pessoas que SEGUEM esse usuário (a query muda aqui)
$stmt_lista = $conn->prepare("SELECT u.id, u.nome, u.foto_perfil, u.curso 
                             FROM seguidor AS s
                             JOIN usuario AS u ON s.id_seguidor = u.id
                             WHERE s.id_seguido = ?"); // <-- A mudança está aqui
$stmt_lista->bind_param("i", $id_usuario_lista);
$stmt_lista->execute();
$resultado_lista = $stmt_lista->get_result();

?>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguidores de <?php echo htmlspecialchars($nome_usuario); ?></title>
    <link rel="stylesheet" href="style.css">
    <!--ICONE-->
    <link rel="shortcut icon" href="https://hotmilk.pucpr.br/inovacao-pucpr/wp-content/uploads/2022/09/simbolo-PUCPR-branco-sem-escrito.svg" type="image/x-icon">
    </head>
<body>
    <header>
        <nav class="cabecalho">
            <div class="logo_maneira">...</div>
            <div class="botoes_maneiros">
                <button id="theme-toggle" class="botao">Mudar Tema</button>
                <a href="feed.php" class="botao botao_feed">FEED</a>
                <a href="perfil.php" class="botao botao_perfil">MEU PERFIL</a>
            </div>
        </nav>
    </header>
    <main class="lista-usuarios-container">
        <h1>Seguidores (<?php echo $resultado_lista->num_rows; ?>)</h1>
        <p>Pessoas que seguem <?php echo htmlspecialchars($nome_usuario); ?>.</p>

        <div class="lista-usuarios">
            <?php if ($resultado_lista->num_rows > 0): ?>
                <?php while($usuario = $resultado_lista->fetch_assoc()): ?>
                    <a href="ver_perfil.php?id=<?php echo $usuario['id']; ?>" class="usuario-item-link">
                        <div class="usuario-item">
                            <img src="<?php echo !empty($usuario['foto_perfil']) ? 'uploads/' . htmlspecialchars($usuario['foto_perfil']) : 'imagens/avatar_padrao.png'; ?>" alt="Foto de <?php echo htmlspecialchars($usuario['nome']); ?>">
                            <div class="usuario-info">
                                <strong><?php echo htmlspecialchars($usuario['nome']); ?></strong>
                                <span><?php echo htmlspecialchars($usuario['curso'] ?? 'Sem curso'); ?></span>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <p><?php echo htmlspecialchars($nome_usuario); ?> não tem seguidores.</p>
            <?php endif; ?>
        </div>
    </main>
    <?php $conn->close(); ?>
    <script src="main.js"></script>
</body>
</html>