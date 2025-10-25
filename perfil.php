<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: entrar.html');
    exit();
}

$servidor = "localhost"; $usuario_db = "root"; $senha_db = ""; $banco = "puc_inside";
$conn = mysqli_connect($servidor, $usuario_db, $senha_db, $banco);

$id_usuario_logado = $_SESSION['usuario_id'];

// 1. Busca os dados do perfil
$stmt_usuario = $conn->prepare("SELECT nome, email, tipo, curso, foto_perfil, data_cadastro FROM usuario WHERE id = ?");
$stmt_usuario->bind_param("i", $id_usuario_logado);
$stmt_usuario->execute();
$resultado_usuario = $stmt_usuario->get_result();
$usuario = $resultado_usuario->fetch_assoc();
$stmt_usuario->close();

// 2. Conta quantos o usuário SEGUE
$stmt_seguindo = $conn->prepare("SELECT COUNT(*) AS total FROM seguidor WHERE id_seguidor = ?");
$stmt_seguindo->bind_param("i", $id_usuario_logado);
$stmt_seguindo->execute();
$total_seguindo = $stmt_seguindo->get_result()->fetch_assoc()['total'];
$stmt_seguindo->close();

// 3. Conta quantos SEGUIDORES o usuário tem
$stmt_seguidores = $conn->prepare("SELECT COUNT(*) AS total FROM seguidor WHERE id_seguido = ?");
$stmt_seguidores->bind_param("i", $id_usuario_logado);
$stmt_seguidores->execute();
$total_seguidores = $stmt_seguidores->get_result()->fetch_assoc()['total'];
$stmt_seguidores->close();

$conn->close();
?>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUCPR Inside - PERFIL</title>
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
                <a href="postar.php" class="botao botao_postar">POSTAR</a>
                <a href="sair.php" class="botao botao_sair">SAIR</a>
            </div>
        </nav>
    </header>

    <main>
        <section id="cadastro-container" class="perfil-container-centralizado">
            <h1 class="titulo_charmoso">Perfil de <?php echo htmlspecialchars($usuario['nome']); ?></h1>

            <div class="perfil-foto">
                <?php if (!empty($usuario['foto_perfil'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" class="foto_perfil" alt="Foto de Perfil">
                <?php else: ?>
                    <img src="imagens/avatar_padrao.png" class="foto_perfil" alt="Foto de Perfil Padrão">
                <?php endif; ?>
            </div>

            <div class="perfil-botoes-social">
                <a href="lista_seguidores.php?id=<?php echo $id_usuario_logado; ?>" class="botao botao_outline">
                    <strong><?php echo $total_seguidores; ?></strong> Seguidores
                </a>
                <a href="lista_seguindo.php?id=<?php echo $id_usuario_logado; ?>" class="botao botao_outline">
                    <strong><?php echo $total_seguindo; ?></strong> Seguindo
                </a>
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
    </main>
    <script src="main.js"></script>
</body>
</html>