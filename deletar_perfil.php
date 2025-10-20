<?php
session_start();

if (!isset($_SESSION['usuario_id'])) { exit('Acesso negado.'); }

    // CRIA A CONEXAO COM O BANCO DE DADOS
    // $conn = mysqli_connect("localhost:3306", "root", "PUC@1234", "puc_inside"); //VERSAO WORKBENCH
    $conn = mysqli_connect("localhost:3307", "root", "", "puc_inside"); //VERSAO XAMPP

$id_usuario = $_SESSION['usuario_id'];

// DELETAR PERFIL
$conn->begin_transaction();
try {
    // 1. Deletar postagens e suas dependências
    $res_posts = $conn->query("SELECT id FROM postagem WHERE id_usuario = $id_usuario");
    while ($post = $res_posts->fetch_assoc()) {
        $id_post = $post['id'];
        $conn->query("DELETE FROM midia WHERE id_postagem = $id_post");
        $conn->query("DELETE FROM likes WHERE id_postagem = $id_post");
        $conn->query("DELETE FROM comentario WHERE id_postagem = $id_post");
    }
    $conn->query("DELETE FROM postagem WHERE id_usuario = $id_usuario");

    // 2. Deletar comentários, likes e follows feitos pelo usuário
    $conn->query("DELETE FROM comentario WHERE id_usuario = $id_usuario");
    $conn->query("DELETE FROM likes WHERE id_usuario = $id_usuario");
    $conn->query("DELETE FROM seguidor WHERE id_seguidor = $id_usuario OR id_seguido = $id_usuario");

    // 3. Finalmente, deletar o usuário
    $stmt = $conn->prepare("DELETE FROM usuario WHERE id = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    
    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    die("Erro ao deletar perfil.");
}
$conn->close();

// Destruir a sessão e redirecionar
session_unset();
session_destroy();
header("Location: index.php?conta_deletada=true");
exit();
?>