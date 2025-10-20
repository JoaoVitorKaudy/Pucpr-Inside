<?php
// Inicia a sessão
session_start();

// Se o usuário não estiver logado, redireciona para a página de login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: entrar.html');
    exit();
}

    // CRIA A CONEXAO COM O BANCO DE DADOS
    // $conn = mysqli_connect("localhost:3306", "root", "PUC@1234", "puc_inside");
    $conn = mysqli_connect("localhost:3307", "root", "", "puc_inside");

// --- 1. Busca os dados principais do usuário logado ---
$id_usuario_logado = $_SESSION['usuario_id'];
$stmt_usuario = $conn->prepare("SELECT nome, email, tipo, curso, foto_perfil, data_cadastro FROM usuario WHERE id = ?");
$stmt_usuario->bind_param("i", $id_usuario_logado);
$stmt_usuario->execute();
$resultado_usuario = $stmt_usuario->get_result();
$usuario = $resultado_usuario->fetch_assoc();
$stmt_usuario->close();

// --- 2. Busca a lista de usuários que a pessoa logada segue ---
$stmt_seguindo = $conn->prepare("SELECT u.id, u.nome, u.foto_perfil 
                                 FROM seguidor AS s
                                 JOIN usuario AS u ON s.id_seguido = u.id
                                 WHERE s.id_seguidor = ?");
$stmt_seguindo->bind_param("i", $id_usuario_logado);
$stmt_seguindo->execute();
$resultado_seguindo = $stmt_seguindo->get_result();

// Não fechamos a conexão ainda, pois usamos ela no HTML
?>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUCPR Inside - PERFIL</title>
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
                <a href="feed.php" class="botao botao_feed">FEED</a>
                <a href="postar.php" class="botao botao_postar">POSTAR</a>
                <a href="sair.php" class="botao botao_sair">SAIR</a>
            </div>
        </nav>
    </header>

    <main class="perfil-main-container">
        <section id="cadastro-container" class="perfil-container">
            <h1 class="titulo_charmoso">Perfil de <?php echo htmlspecialchars($usuario['nome']); ?></h1>

            <div class="perfil-foto">
                <?php if (!empty($usuario['foto_perfil'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" class="foto_perfil" alt="Foto de Perfil">
                <?php else: ?>
                    <img src="imagens/avatar_padrao.png" class="foto_perfil" alt="Foto de Perfil Padrão">
                <?php endif; ?>
            </div>

            <div class="perfil-dados">
                <p class="informacion"><strong>Nome:</strong> <?php echo htmlspecialchars($usuario['nome']); ?></p>
                <p class="informacion"><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
                <p class="informacion"><strong>Tipo:</strong> <?php echo htmlspecialchars($usuario['tipo'] ?? 'Não informado'); ?></p>
                <p class="informacion"><strong>Curso:</strong> <?php echo htmlspecialchars($usuario['curso'] ?? 'Não informado'); ?></p>
                <p class="informacion"><strong>Membro desde:</strong> <?php echo date('d/m/Y', strtotime($usuario['data_cadastro'])); ?></p>
            </div>

            <a href="atualizar_perfil.php" class="botao botao_atualizar">Atualizar Dados</a>
        </section>

        <aside class="seguindo-container">
            <h2>Seguindo</h2>
            
            <?php if ($resultado_seguindo->num_rows > 0): ?>
                <ul class="lista-seguindo">
                    <?php while($seguindo = $resultado_seguindo->fetch_assoc()): ?>
                        <a href="ver_perfil.php?id=<?php echo $seguindo['id']; ?>" class="seguindo-link">
                            <li class="seguindo-item">
                                <img src="<?php echo !empty($seguindo['foto_perfil']) ? 'uploads/' . htmlspecialchars($seguindo['foto_perfil']) : 'imagens/avatar_padrao.png'; ?>" alt="Foto de <?php echo htmlspecialchars($seguindo['nome']); ?>">
                                <span><?php echo htmlspecialchars($seguindo['nome']); ?></span>
                            </li>
                        </a>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>Você ainda não segue ninguém.</p>
            <?php endif; ?>
        </aside>

    </main>
    <?php
        $stmt_seguindo->close();
        $conn->close();
    ?>
</body>
</html>