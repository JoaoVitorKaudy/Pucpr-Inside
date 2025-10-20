<?php
session_start();

if (!isset($_SESSION['usuario_id']) || !isset($_GET['id_postagem'])) {
    exit('Acesso negado ou dados insuficientes.');
}

    // CRIA A CONEXAO COM O BANCO DE DADOS
    // $conn = mysqli_connect("localhost:3306", "root", "PUC@1234", "puc_inside"); //VERSAO WORKBENCH
    $conn = mysqli_connect("localhost:3307", "root", "", "puc_inside"); //VERSAO XAMPP

$id_postagem = $_GET['id_postagem'];
$id_usuario = $_SESSION['usuario_id'];

$conn->begin_transaction();

try {
    // 1. Verifica se o like já existe
    $stmt_check = $conn->prepare("SELECT id_usuario FROM likes WHERE id_usuario = ? AND id_postagem = ?");
    $stmt_check->bind_param("ii", $id_usuario, $id_postagem);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // 2. Se existe, remove o like (descurtir)
        $stmt_delete = $conn->prepare("DELETE FROM likes WHERE id_usuario = ? AND id_postagem = ?");
        $stmt_delete->bind_param("ii", $id_usuario, $id_postagem);
        $stmt_delete->execute();

        // E decrementa o contador
        $stmt_update = $conn->prepare("UPDATE postagem SET qtde_gostei = qtde_gostei - 1 WHERE id = ? AND qtde_gostei > 0");
        $stmt_update->bind_param("i", $id_postagem);
        $stmt_update->execute();

    } else {
        // 3. Se não existe, adiciona o like (curtir)
        $stmt_insert = $conn->prepare("INSERT INTO likes (id_usuario, id_postagem) VALUES (?, ?)");
        $stmt_insert->bind_param("ii", $id_usuario, $id_postagem);
        $stmt_insert->execute();

        // E incrementa o contador
        $stmt_update = $conn->prepare("UPDATE postagem SET qtde_gostei = qtde_gostei + 1 WHERE id = ?");
        $stmt_update->bind_param("i", $id_postagem);
        $stmt_update->execute();
    }
    
    $conn->commit();

} catch (Exception $e) {
    $conn->rollback();
    error_log("Erro ao processar like: " . $e->getMessage());
}

$conn->close();

header("Location: feed.php#post-" . $id_postagem);
exit();
?>