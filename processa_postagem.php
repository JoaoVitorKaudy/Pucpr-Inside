<?php
session_start();

// Verifica se o usuário está logado, senão, nega o acesso.
if (!isset($_SESSION['usuario_id'])) {
    exit('Acesso negado. Por favor, faça login.');
}

// Verifica se o formulário foi enviado pelo método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // CRIA A CONEXAO COM O BANCO DE DADOS
    // $conn = mysqli_connect("localhost:3306", "root", "PUC@1234", "puc_inside");
    $conn = mysqli_connect("localhost:3307", "root", "", "puc_inside");

    // Pega os dados do formulário e da sessão
    $conteudo = trim($_POST['conteudo']);
    $id_usuario = $_SESSION['usuario_id'];

    // --- Validação Simples ---
    if (empty($conteudo)) {
        die("O conteúdo da postagem não pode estar vazio. Volte e tente novamente.");
    }

    // --- Lógica de Inserção no Banco de Dados ---
    // Usaremos uma transação para garantir que a postagem e a mídia sejam salvas juntas, ou nenhuma delas.
    $conn->begin_transaction();

    try {
        // 1. Insere na tabela 'postagem'
        $stmt_post = $conn->prepare("INSERT INTO postagem (conteudo, id_usuario) VALUES (?, ?)");
        $stmt_post->bind_param("si", $conteudo, $id_usuario);
        $stmt_post->execute();

        // Pega o ID da postagem que acabamos de criar
        $post_id = $conn->insert_id;

        // 2. Verifica se um arquivo de mídia foi enviado
        if (isset($_FILES['midia_post']) && $_FILES['midia_post']['error'] == 0) {
            $pasta_uploads = 'uploads/'; // A mesma pasta das fotos de perfil

            // Garante que o diretório exista e tenha as permissões corretas
            if (!is_dir($pasta_uploads)) {
                mkdir($pasta_uploads, 0777, true);
            }
            
            $midia = $_FILES['midia_post'];
            $nome_arquivo = uniqid() . '_' . basename($midia['name']);
            $caminho_completo = $pasta_uploads . $nome_arquivo;

            // Move o arquivo para a pasta de uploads
            if (move_uploaded_file($midia['tmp_name'], $caminho_completo)) {
                
                // 3. Se o upload deu certo, insere na tabela 'midia'
                $stmt_midia = $conn->prepare("INSERT INTO midia (nome_arquivo, tipo, tamanho_bytes, id_postagem) VALUES (?, ?, ?, ?)");
                $stmt_midia->bind_param("ssii", $nome_arquivo, $midia['type'], $midia['size'], $post_id);
                $stmt_midia->execute();
                
            } else {
                // Se falhar ao mover o arquivo, joga um erro para cancelar a transação
                throw new Exception("Falha ao fazer upload da mídia.");
            }
        }

        // Se tudo deu certo até aqui, confirma as alterações no banco
        $conn->commit();
        
        // Redireciona para o feed para o usuário ver sua nova postagem
        header("Location: feed.php");
        exit();

    } catch (Exception $e) {
        // Se algo deu errado, desfaz todas as alterações
        $conn->rollback();
        die("Erro ao criar a postagem: " . $e->getMessage());
    }

    $conn->close();
} else {
    // Se alguém tentar acessar este arquivo diretamente, redireciona
    header("Location: postar.php");
    exit();
}
?>