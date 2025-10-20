<?php
session_start();

// Apenas usuários logados podem seguir outros
if (!isset($_SESSION['usuario_id'])) {
    exit('Acesso negado.');
}

// Verifica se os parâmetros necessários foram enviados
if (!isset($_GET['id_seguido']) || !isset($_GET['action']) || !isset($_GET['id_postagem'])) {
    exit('Parâmetros inválidos.');
}

    // CRIA A CONEXAO COM O BANCO DE DADOS
    // $conn = mysqli_connect("localhost:3306", "root", "PUC@1234", "puc_inside");
    $conn = mysqli_connect("localhost:3307", "root", "", "puc_inside");

$id_seguidor = $_SESSION['usuario_id']; // Quem está fazendo a ação
$id_seguido = $_GET['id_seguido'];     // Quem está recebendo a ação
$action = $_GET['action'];             // A ação a ser feita ('seguir' ou 'deixar_de_seguir')

// --- VALIDAÇÃO DE SEGURANÇA: Um usuário não pode seguir a si mesmo ---
if ($id_seguidor == $id_seguido) {
    // Redireciona de volta sem fazer nada
    header("Location: feed.php#post-" . $_GET['id_postagem']);
    exit();
}

// Processa a ação
if ($action == 'seguir') {
    // Tenta inserir a relação. INSERT IGNORE previne erros se a relação já existir.
    $stmt = $conn->prepare("INSERT IGNORE INTO seguidor (id_seguidor, id_seguido) VALUES (?, ?)");
    $stmt->bind_param("ii", $id_seguidor, $id_seguido);
    $stmt->execute();
    
} elseif ($action == 'deixar_de_seguir') {
    // Remove a relação da tabela
    $stmt = $conn->prepare("DELETE FROM seguidor WHERE id_seguidor = ? AND id_seguido = ?");
    $stmt->bind_param("ii", $id_seguidor, $id_seguido);
    $stmt->execute();
}

$conn->close();

// Redireciona o usuário de volta para a postagem específica no feed
header("Location: feed.php#post-" . $_GET['id_postagem']);
exit();
?>