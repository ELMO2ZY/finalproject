// auth.js
document.addEventListener('DOMContentLoaded', function() {
    const container = document.querySelector('.auth-container');
    const loginForm = document.querySelector('.login-form');
    const registerForm = document.querySelector('.register-form');
    const switchBtns = document.querySelectorAll('.switch-btn');

    // Switch to Register form
    switchBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            container.classList.toggle('register-mode');
            
            // Update URL without reload
            if (container.classList.contains('register-mode')) {
                history.pushState(null, '', '?register=true');
            } else {
                history.pushState(null, '', window.location.pathname);
            }
        });
    });

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        const isRegister = new URLSearchParams(window.location.search).has('register');
        if (isRegister) {
            container.classList.add('register-mode');
        } else {
            container.classList.remove('register-mode');
        }
    });

    // Focus first input when form is shown
    const forms = document.querySelectorAll('.auth-form');
    forms.forEach(form => {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'style') {
                    const formStyle = window.getComputedStyle(form);
                    if (formStyle.transform === 'matrix(1, 0, 0, 1, 0, 0)') {
                        const input = form.querySelector('input');
                        if (input) input.focus();
                    }
                }
            });
        });
        
        observer.observe(form, {
            attributes: true,
            attributeFilter: ['style']
        });
    });
});