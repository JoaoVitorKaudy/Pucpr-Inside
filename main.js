document.addEventListener('DOMContentLoaded', function() {

    //LÓGICA PARA O FORMULÁRIO DE CADASTRO BACANA
    const formCadastro = document.getElementById('form_cadastro');
    if (formCadastro) {
        const divMensagemCadastro = document.getElementById('mensagem-sucesso');

        formCadastro.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(formCadastro);

            fetch('api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                divMensagemCadastro.className = 'mensagem';
                if (data.status === 'sucesso') {
                    divMensagemCadastro.classList.add('sucesso');
                    formCadastro.reset();
                } else {
                    divMensagemCadastro.classList.add('erro');
                }
                divMensagemCadastro.innerHTML = data.message;
                divMensagemCadastro.style.display = 'block';
            })
            .catch(error => console.error('Erro na requisição de cadastro:', error));
        });
    }


    // LÓGICA PARA O FORMULÁRIO DE LOGIN BACANA
    const formLogin = document.getElementById('form_login');
    if (formLogin) {
        const divMensagemLogin = document.getElementById('mensagem-login');

        formLogin.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(formLogin);

            fetch('login_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'sucesso') {
                    divMensagemLogin.className = 'mensagem sucesso';
                    divMensagemLogin.innerHTML = data.message;
                    divMensagemLogin.style.display = 'block';

                    setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 1500);

                } else {
                    divMensagemLogin.className = 'mensagem erro';
                    divMensagemLogin.innerHTML = data.message;
                    divMensagemLogin.style.display = 'block';
                }
            })
            .catch(error => console.error('Erro na requisição de login:', error));
        });
    }

});