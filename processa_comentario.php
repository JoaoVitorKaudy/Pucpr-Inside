<?php
session_start();

// Apenas usuários logados podem comentar
if (!isset($_SESSION['usuario_id'])) {
    exit('Acesso negado.');
}

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validação dos dados
    if (empty(trim($_POST['assunto'])) || empty($_POST['id_postagem'])) {
        die("Comentário ou ID da postagem inválido.");
    }

    // CRIA A CONEXAO COM O BANCO DE DADOS
    // $conn = mysqli_connect("localhost:3306", "root", "PUC@1234", "puc_inside");
    $conn = mysqli_connect("localhost:3307", "root", "", "puc_inside");

    // Pega os dados
    $assunto = $_POST['assunto'];
    $id_postagem = $_POST['id_postagem'];
    $id_usuario = $_SESSION['usuario_id'];

    // Insere o comentário no banco de dados usando prepared statements
    $stmt = $conn->prepare("INSERT INTO comentario (assunto, id_usuario, id_postagem) VALUES (?, ?, ?)");
    $stmt->bind_param("sii", $assunto, $id_usuario, $id_postagem);

    if ($stmt->execute()) {
        // Redireciona de volta para o feed, pulando diretamente para a postagem comentada
        header("Location: feed.php#post-" . $id_postagem);
        exit();
    } else {
        echo "Erro ao salvar o comentário: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>