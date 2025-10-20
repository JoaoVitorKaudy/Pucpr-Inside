<?php
// Inicia a sessão
session_start();

// Se o usuário não estiver logado, redireciona
if (!isset($_SESSION['usuario_id'])) {
    header('Location: entrar.html');
    exit();
}

    // CRIA A CONEXAO COM O BANCO DE DADOS
    // $conn = mysqli_connect("localhost:3306", "root", "PUC@1234", "puc_inside");
    $conn = mysqli_connect("localhost:3307", "root", "", "puc_inside");

// Busca os dados atuais do usuário para preencher o formulário
$id_usuario_logado = $_SESSION['usuario_id'];
$stmt = $conn->prepare("SELECT nome, tipo, curso FROM usuario WHERE id = ?");
$stmt->bind_param("i", $id_usuario_logado);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$stmt->close();
$conn->close();

?>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUCPR Inside - Atualizar Perfil</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav class="cabecalho">
            <div class="logo_maneira">
                <img src="https://hotmilk.pucpr.br/inovacao-pucpr/wp-content/uploads/2022/09/simbolo-PUCPR-branco-sem-escrito.svg" alt="PUCPR Logo">
                <h2>PUCPR Inside</h2>
            </div>
            <div class="botoes_maneiros">
                <a href="perfil.php" class="botao botao_perfil">MEU PERFIL</a>
                <a href="postar.php" class="botao botao_postar">POSTAR</a>
                <a href="sair.php" class="botao botao_sair">SAIR</a>
            </div>
        </nav>
    </header>

    <main>
        <section id="cadastro-container">
            <h2>Atualizar Perfil</h2>
            <form action="processa_atualizacao.php" method="post" enctype="multipart/form-data" id="form_cadastro">

                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>

                <label for="senha">Nova Senha (deixe em branco para não alterar):</label>
                <input type="password" id="senha" name="senha" placeholder="Digite a nova senha">

                <label for="confirmar_senha">Confirmar Nova Senha:</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Confirme a nova senha">
                
                <label for="tipo">Tipo de Usuário:</label>
                <select id="tipo" name="tipo">
                    <option value="aluno" <?php if ($usuario['tipo'] == 'aluno') echo 'selected'; ?>>Aluno</option>
                    <option value="professor" <?php if ($usuario['tipo'] == 'professor') echo 'selected'; ?>>Professor</option>
                </select>

                <label for="curso">Curso:</label>
                <input type="text" id="curso" name="curso" value="<?php echo htmlspecialchars($usuario['curso']); ?>" placeholder="Ex: Engenharia de Software">

                <label for="foto_perfil">Nova Foto de Perfil:</label>
                <input type="file" id="foto_perfil" name="foto_perfil">

                <button type="submit">Salvar Alterações</button>
            </form>

            <div class="zona_perigosa">
                <h3>☠ Zona de Perigo ☠</h3>
                <p>Esta ação é irreversível. Todos os seus dados, postagens e comentários serão permanentemente apagados.</p>
                <a href="deletar_perfil.php" class="btn_deletar" onclick="return confirm('ATENÇÃO!\n\nVocê tem certeza que deseja deletar permanentemente sua conta? Esta ação não pode ser desfeita.');">Deletar Minha Conta</a>
            </div>
        </section>
    </main>
</body>
</html>