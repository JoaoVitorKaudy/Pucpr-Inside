<?php
    // ===== ADIÇÃO PARA DEBUG: MOSTRAR ERROS DO PHP =====
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    // ===================================================

    // PARA A MENSAGEM RESPOSTA DO CADASTRO
    header('Content-Type: application/json');
    $response = array();

    // CRIA A CONEXAO COM O BANCO DE DADOS
    $conn = mysqli_connect("localhost:3306", "root", "PUC@1234", "puc_inside");

    // VERIFICA SE A CONEXÃO FALHOU
    if (!$conn) {
        $response['status'] = 'erro';
        $response['message'] = 'Erro de conexão com o banco de dados: ' . mysqli_connect_error();
        echo json_encode($response);
        exit();
    }

    // QUANDO UM USUARIO FOR CADASTRADO
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $stmt = $conn->prepare("INSERT INTO usuario (nome, email, senha) VALUES (?, ?, ?)");
        
        $nome = $_POST["nome"];
        $email = $_POST["email"];
        // CRIPTOGRAFIA DA SENHA
        $senha_hash = password_hash($_POST["senha"], PASSWORD_DEFAULT);
        
        $stmt->bind_param("sss", $nome, $email, $senha_hash);
        
        if ($stmt->execute()) {
            $response['status'] = 'sucesso';
            $response['message'] = 'Usuário cadastrado com sucesso!';
        } else {
            $response['status'] = 'erro';
            $response['message'] = 'Erro ao cadastrar usuário: ' . $stmt->error;
        }

        $stmt->close();

    } else {
        $response['status'] = 'erro';
        $response['message'] = 'Método de requisição inválido.';
    }

    $conn->close();
    
    echo json_encode($response);
?>