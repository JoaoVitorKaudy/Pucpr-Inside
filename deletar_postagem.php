<?php
session_start();

if (!isset($_SESSION['usuario_id']) || !isset($_GET['id_postagem'])) {
    exit('Acesso negado.');
}

    // CRIA A CONEXAO COM O BANCO DE DADOS
    // $conn = mysqli_connect("localhost:3306", "root", "PUC@1234", "puc_inside"); //VERSAO WORKBENCH
    $conn = mysqli_connect("localhost:3307", "root", "", "puc_inside"); //VERSAO XAMPP

$id_postagem = $_GET['id_postagem'];
$id_usuario = $_SESSION['usuario_id'];

$conn->begin_transaction();
try {
    // DELETAR EM ORDEM para não violar as chaves estrangeiras
    $conn->query("DELETE FROM midia WHERE id_postagem = $id_postagem");
    $conn->query("DELETE FROM likes WHERE id_postagem = $id_postagem");
    $conn->query("DELETE FROM comentario WHERE id_postagem = $id_postagem");
    
    // Finalmente, deleta a postagem, garantindo que o dono é quem está deletando
    $stmt = $conn->prepare("DELETE FROM postagem WHERE id = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $id_postagem, $id_usuario);
    $stmt->execute();
    
    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    die("Erro ao deletar postagem.");
}

$conn->close();
header("Location: feed.php");
exit();
?>