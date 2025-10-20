<?php
session_start();

// Se o usuário não estiver logado, não faz nada
if (!isset($_SESSION['usuario_id'])) {
    exit('Acesso negado.');
}

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // CRIA A CONEXAO COM O BANCO DE DADOS
    // $conn = mysqli_connect("localhost:3306", "root", "PUC@1234", "puc_inside");
    $conn = mysqli_connect("localhost:3307", "root", "", "puc_inside");

    // Pega os dados do formulário
    $id_usuario = $_SESSION['usuario_id'];
    $nome = $_POST['nome'];
    $nova_senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    $tipo = $_POST['tipo'];
    $curso = $_POST['curso'];

    // --- VALIDAÇÃO ---
    if (!empty($nova_senha) && $nova_senha !== $confirmar_senha) {
        die("As senhas não coincidem. Volte e tente novamente.");
    }

    // --- LÓGICA DE ATUALIZAÇÃO ---
    // Começamos a montar a query SQL
    $sql = "UPDATE usuario SET nome = ?, tipo = ?, curso = ?";
    $params = [$nome, $tipo, $curso];
    $types = "sss";

    // Se uma nova senha foi fornecida, adiciona à query
    if (!empty($nova_senha)) {
        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        $sql .= ", senha = ?";
        $params[] = $senha_hash;
        $types .= "s";
    }

    // Se uma nova foto de perfil foi enviada
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
        $pasta_uploads = 'uploads/';
        // Cria um nome de arquivo único para evitar conflitos
        $nome_arquivo_foto = uniqid() . '_' . basename($_FILES['foto_perfil']['name']);
        $caminho_completo = $pasta_uploads . $nome_arquivo_foto;

        // Move o arquivo para a pasta de uploads
        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $caminho_completo)) {
            $sql .= ", foto_perfil = ?";
            $params[] = $nome_arquivo_foto;
            $types .= "s";
        }
    }

    // Adiciona o ID do usuário no final da query e dos parâmetros
    $sql .= " WHERE id = ?";
    $params[] = $id_usuario;
    $types .= "i";

    // Executa a query com prepared statements
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params); // O operador ... desempacota o array de parâmetros
    
    if ($stmt->execute()) {
        // Atualiza o nome na sessão para que o cabeçalho mostre o nome novo imediatamente
        $_SESSION['usuario_nome'] = $nome;
        
        // Redireciona de volta para a página de perfil
        header("Location: perfil.php");
        exit();
    } else {
        echo "Erro ao atualizar os dados: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>