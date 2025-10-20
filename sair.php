<?php
// 1. Inicia a sessão para poder manipulá-la
session_start();

// 2. Remove todas as variáveis da sessão (limpa os dados como 'usuario_id', 'usuario_nome', etc.)
session_unset();

// 3. Destrói completamente a sessão do servidor
session_destroy();

// 4. Redireciona o usuário para a página inicial (index.html)
header("Location: index.php");
exit(); // Garante que nenhum outro código seja executado após o redirecionamento
?>