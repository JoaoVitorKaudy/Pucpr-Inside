<?php
session_start();

if (!isset($_SESSION['usuario_id']) || !isset($_GET['id_comentario'])) {
    exit('Acesso negado.');
}

    // CRIA A CONEXAO COM O BANCO DE DADOS
    // $conn = mysqli_connect("localhost:3306", "root", "PUC@1234", "puc_inside"); //VERSAO WORKBENCH
    $conn = mysqli_connect("localhost:3307", "root", "", "puc_inside"); //VERSAO XAMPP

$id_comentario = $_GET['id_comentario'];
$id_usuario = $_SESSION['usuario_id'];

// Deleta o comentário, garantindo que o dono é quem está deletando
$stmt = $conn->prepare("DELETE FROM comentario WHERE id = ? AND id_usuario = ?");
$stmt->bind_param("ii", $id_comentario, $id_usuario);
$stmt->execute();

$conn->close();
// Pega o ID da postagem para poder voltar para a âncora certa
$id_postagem = $_GET['id_postagem'];
header("Location: feed.php#post-" . $id_postagem);
exit();
?>