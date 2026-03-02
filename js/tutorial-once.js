/**
 * Tutorial do Rádio - Aparece apenas 1x
 */

(function() {
    'use strict';
    
    const STORAGE_KEY = 'memorias_tutorial_seen';
    const overlay = document.getElementById('tutorialOverlay');
    const closeBtn = document.getElementById('closeTutorial');
    
    if (!overlay) return;
    
    // Verifica se já viu o tutorial
    function hasSeen() {
        return localStorage.getItem(STORAGE_KEY) === 'true';
    }
    
    // Marca como visto
    function markAsSeen() {
        localStorage.setItem(STORAGE_KEY, 'true');
        localStorage.setItem('memorias_tutorial_date', new Date().toISOString());
    }
    
    // Fecha o tutorial
    function closeTutorial() {
        overlay.style.transition = 'opacity 0.3s';
        overlay.style.opacity = '0';
        setTimeout(() => {
            overlay.style.display = 'none';
        }, 300);
        markAsSeen();
    }
    
    // Inicialização
    if (hasSeen()) {
        // Já viu antes - não mostra
        overlay.style.display = 'none';
    } else {
        // Primeira vez - mostra
        overlay.style.display = 'flex';
    }
    
    // Botão fechar
    if (closeBtn) {
        closeBtn.addEventListener('click', closeTutorial);
    }
    
    // Clique fora fecha (opcional)
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            closeTutorial();
        }
    });
    
})();
