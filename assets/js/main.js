document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('.main-nav');

    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            nav.classList.toggle('is-open');
        });

        nav.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                nav.classList.remove('is-open');
            });
        });
    }

    const passwordInput = document.querySelector('[data-password]');
    const togglePassword = document.querySelector('[data-toggle-password]');
    if (passwordInput && togglePassword) {
        togglePassword.addEventListener('click', function () {
            const hidden = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', hidden ? 'text' : 'password');
            togglePassword.textContent = hidden ? 'Ocultar' : 'Mostrar';
        });
    }
});
