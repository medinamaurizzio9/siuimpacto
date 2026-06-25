(function () {
    function createToggle(input) {
        if (!input || input.dataset.passwordToggleReady === 'true') {
            return;
        }

        input.dataset.passwordToggleReady = 'true';

        var wrapper = document.createElement('div');
        wrapper.className = 'password-toggle-wrapper';

        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        input.classList.add('password-toggle-input');

        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'password-toggle-button';
        button.setAttribute('aria-label', 'Mostrar contraseña');
        button.setAttribute('title', 'Mostrar contraseña');
        button.textContent = '👁';

        button.addEventListener('click', function () {
            var isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            button.textContent = isHidden ? '👁‍🗨' : '👁';
            button.setAttribute('aria-label', isHidden ? 'Ocultar contraseña' : 'Mostrar contraseña');
            button.setAttribute('title', isHidden ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });

        wrapper.appendChild(button);
    }

    function initPasswordToggles() {
        document.querySelectorAll('input[type="password"], input[data-password-toggle], input.password-toggle')
            .forEach(createToggle);
    }

    document.addEventListener('DOMContentLoaded', initPasswordToggles);
})();
