jQuery(document).ready(function($) {
    // Adicionar tratamento de erros
    $(document).ajaxError(function(event, jqXHR, settings, error) {
        console.error('Erro na requisição AJAX:', error);
    });

    // Adicionar feedback visual durante o login
    $('.google-login-button').on('click', function() {
        $(this).addClass('loading');
    });

    // Verificar se há mensagens de erro na URL
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('error')) {
        var errorMessage = urlParams.get('error');
        alert('Erro no login: ' + errorMessage);
    }
}); 