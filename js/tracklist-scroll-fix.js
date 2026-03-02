/**
 * Fix: Scroll automático para o rádio ao clicar na tracklist
 * Adicionar no final do radio-interface.js ou como script separado
 */

(function() {
    'use strict';
    
    const tracklist = document.getElementById('tracklist');
    const radioContainer = document.querySelector('.radio-container');
    
    if (!tracklist || !radioContainer) return;
    
    // Quando clicar em qualquer faixa da tracklist
    tracklist.addEventListener('click', function(e) {
        const trackItem = e.target.closest('li[data-track]');
        
        if (trackItem) {
            // Aguarda um momento para a mensagem aparecer
            setTimeout(() => {
                // Scroll suave até o rádio
                radioContainer.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }, 100);
        }
    });
    
})();
