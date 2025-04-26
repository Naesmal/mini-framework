/**
 * Mini-Framework PHP - Script principal
 */

document.addEventListener('DOMContentLoaded', function() {
    // Fade out des messages flash après 5 secondes
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 1s';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 1000);
        }, 5000);
    });

    // Animation des liens de navigation
    const navLinks = document.querySelectorAll('header nav a');
    navLinks.forEach(link => {
        if (window.location.pathname === link.getAttribute('href')) {
            link.classList.add('active');
            link.style.fontWeight = 'bold';
        }
    });

    // Gestion des formulaires
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        // Ajout de la classe 'error' aux champs invalides
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                field.classList.remove('error');
                
                if (!field.value.trim()) {
                    field.classList.add('error');
                    if (isValid) {
                        field.focus();
                    }
                    isValid = false;
                }
            });

            // Vérification des emails
            const emailFields = form.querySelectorAll('input[type="email"]');
            emailFields.forEach(field => {
                if (field.value.trim() && !isValidEmail(field.value)) {
                    field.classList.add('error');
                    if (isValid) {
                        field.focus();
                    }
                    isValid = false;
                }
            });

            // Vérification des mots de passe (si présents)
            const passwordField = form.querySelector('input[name="password"]');
            const passwordConfirmField = form.querySelector('input[name="password_confirm"]');
            
            if (passwordField && passwordConfirmField && 
                passwordField.value && passwordConfirmField.value &&
                passwordField.value !== passwordConfirmField.value) {
                passwordField.classList.add('error');
                passwordConfirmField.classList.add('error');
                if (isValid) {
                    passwordConfirmField.focus();
                }
                isValid = false;
                
                // Afficher un message d'erreur si les mots de passe ne correspondent pas
                const errorMsg = document.createElement('div');
                errorMsg.className = 'alert alert-danger password-mismatch';
                errorMsg.textContent = 'Les mots de passe ne correspondent pas.';
                
                // Supprimer les messages précédents
                const existingMsg = document.querySelector('.password-mismatch');
                if (existingMsg) {
                    existingMsg.remove();
                }
                
                form.prepend(errorMsg);
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    });

    // Fonction de validation d'email
    function isValidEmail(email) {
        const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return pattern.test(email);
    }

    // Style pour les champs d'erreur
    const style = document.createElement('style');
    style.textContent = `
        input.error, select.error, textarea.error {
            border-color: #e74c3c !important;
            box-shadow: 0 0 0 2px rgba(231, 76, 60, 0.2) !important;
        }
    `;
    document.head.appendChild(style);
});