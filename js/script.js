// js/script.js

document.addEventListener('DOMContentLoaded', function() {
    // Fonctionnalité simple: Cacher les messages de succès ou d'erreur après un certain temps
    const messages = document.querySelectorAll('.success-message, .error-message');
    messages.forEach(message => {
        if (message) {
            setTimeout(() => {
                message.style.opacity = '0';
                message.style.transition = 'opacity 1s ease-out';
                setTimeout(() => {
                    message.style.display = 'none';
                }, 1000); // Laisse le temps à la transition de se terminer
            }, 5000); // Le message disparaît après 5 secondes
        }
    });

    // Vous pouvez ajouter d'autres fonctionnalités JavaScript ici si besoin,
    // mais pour l'instant, nous gardons les choses simples comme demandé.
});