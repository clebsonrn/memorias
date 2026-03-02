/**
 * Gerenciador de Banner LGPD
 * Mostra apenas 1x por sessão/dispositivo
 */

(function() {
    'use strict';
    
    const STORAGE_KEY = 'memorias_lgpd_accepted';
    const banner = document.getElementById('lgpdBanner');
    const acceptBtn = document.getElementById('acceptLgpd');
    
    if (!banner) return;
    
    // Verifica se já aceitou
    function hasAccepted() {
        return localStorage.getItem(STORAGE_KEY) === 'true';
    }
    
    // Salva aceitação
    function saveAcceptance() {
        localStorage.setItem(STORAGE_KEY, 'true');
        localStorage.setItem('memorias_lgpd_date', new Date().toISOString());
    }
    
    // Esconde banner
    function hideBanner() {
        banner.style.transition = 'opacity 0.3s, transform 0.3s';
        banner.style.opacity = '0';
        banner.style.transform = 'translateY(20px)';
        setTimeout(() => {
            banner.style.display = 'none';
        }, 300);
    }
    
    // Mostra banner
    function showBanner() {
        banner.style.display = 'block';
        setTimeout(() => {
            banner.style.opacity = '1';
            banner.style.transform = 'translateY(0)';
        }, 10);
    }
    
    // Inicialização
    if (hasAccepted()) {
        banner.style.display = 'none';
    } else {
        showBanner();
    }
    
    // Botão aceitar
    if (acceptBtn) {
        acceptBtn.addEventListener('click', () => {
            saveAcceptance();
            hideBanner();
        });
    }
    
})();
