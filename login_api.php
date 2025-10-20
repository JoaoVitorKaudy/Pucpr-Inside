<?php
    session_start();

    header('Content-Type: application/json');

    // CRIA A CONEXAO COM O BANCO DE DADOS
    // $conn = mysqli_connect("localhost:3306", "root", "PUC@1234", "puc_inside");
    $conn = mysqli_connect("localhost:3307", "root", "", "puc_inside");

    $response = array();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // 1. BUSCAR USUÁRIO PELO EMAIL
        $email = $_POST["email"];
        $senha_digitada = $_POST["senha"];
        
        $stmt = $conn->prepare("SELECT id, nome, senha, data_cadastro FROM usuario WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // 2. VERIFICAR SE O USUÁRIO EXISTE
        if ($result->num_rows === 1) {
            $usuario = $result->fetch_assoc();
            $senha_hash_db = $usuario['senha'];

            // 3. VERIFICAR SE A SENHA ESTÁ CORRETA
            if (password_verify($senha_digitada, $senha_hash_db)) {
                // Senha correta! Login bem-sucedido.
                
                // 4. GUARDAR INFORMAÇÕES DO USUÁRIO NA SESSÃO
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['data_cadastro'] = $usuario['data_cadastro'];
                
                $response['status'] = 'sucesso';
                $response['message'] = 'Login realizado com sucesso!';
                // Vamos redirecionar o usuário para uma página de "painel"
                $response['redirect_url'] = 'feed.php'; 

            } else {
                $response['status'] = 'erro';
                $response['message'] = 'Email ou senha inválidos.';
            }
        } else {
            $response['status'] = 'erro';
            $response['message'] = 'Email ou senha inválidos.';
        }
        $stmt->close();
    } else {
        $response['status'] = 'erro';
        $response['message'] = 'Método de requisição inválido.';
    }

    $conn->close();
    echo json_encode($response);
?>