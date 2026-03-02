/**
 * Radio Interface - Memórias de um Pé Vermelho
 * Conecta os controles visuais do rádio ao player de áudio
 */

document.addEventListener('DOMContentLoaded', () => {
    // Controle do Welcome Overlay
    initWelcomeOverlay();
    
    // Aguardar player estar pronto
    const checkPlayer = setInterval(() => {
        if (typeof player !== 'undefined') {
            clearInterval(checkPlayer);
            initRadioControls();
        }
    }, 100);
});

/**
 * Inicializa o overlay de boas-vindas
 */
function initWelcomeOverlay() {
    const welcomeOverlay = document.getElementById('welcomeOverlay');
    const startButton = document.getElementById('startButton');
    const powerHint = document.getElementById('powerHint');
    
    // Botão para começar — liga o rádio e dá play automaticamente
    // IMPORTANTE: iOS exige que audio.play() seja chamado direto no evento de clique
    startButton.addEventListener('click', async () => {
        // Fecha overlay
        welcomeOverlay.classList.add('hidden');
        setTimeout(() => { welcomeOverlay.style.display = 'none'; }, 500);
        if (powerHint) powerHint.classList.remove('show');

        if (!player.isOn) {
            const radioWrapper = document.querySelector('.radio-wrapper');
            if (radioWrapper) radioWrapper.classList.add('powered');

            // iOS: inicia o áudio DIRETO no handler do clique (sem setTimeout)
            // Pula o efeito de chiado no mobile para evitar bloqueio do Safari
            try {
                player.isOn = true;
                player.loadTrack(0);
                await player.mainAudio.play();
                player.isPlaying = true;
                player.fadeIn();
                player.updateMediaSessionMetadata();
            } catch (err) {
                console.error('Erro ao iniciar áudio:', err);
                // Fallback: tenta powerOn normal
                player.isOn = false;
                await player.powerOn();
                await player.play();
            }
        }
    });
}

function initRadioControls() {
    console.log('Inicializando controles do rádio...');
    
    // Botões do rádio
    const powerBtn = document.getElementById('powerBtn');
    const playBtn = document.getElementById('playBtn');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    // Tracklist
    const tracklistItems = document.querySelectorAll('.tracklist li');
    
    // Event Listeners
    
    // Power button - Liga/Desliga o rádio
    powerBtn.addEventListener('click', () => {
        console.log('Power button clicked');
        
        // Remove hint se estiver visível
        const powerHint = document.getElementById('powerHint');
        if (powerHint) {
            powerHint.classList.remove('show');
        }
        
        if (player.isOn) {
            player.powerOff();
            addButtonFeedback(powerBtn, 'power-off');
        } else {
            player.powerOn();
            addButtonFeedback(powerBtn, 'power-on');
        }
    });
    
    // Play/Pause button
    playBtn.addEventListener('click', () => {
        console.log('Play button clicked');
        player.togglePlay();
        addButtonFeedback(playBtn, 'play');
    });
    
    // Previous button
    prevBtn.addEventListener('click', () => {
        console.log('Previous button clicked');
        player.playPrevious();
        addButtonFeedback(prevBtn, 'prev');
    });
    
    // Next button
    nextBtn.addEventListener('click', () => {
        console.log('Next button clicked');
        player.playNext();
        addButtonFeedback(nextBtn, 'next');
    });
    
    // Tracklist items
    tracklistItems.forEach((item, index) => {
        item.addEventListener('click', () => {
            console.log(`Tracklist item ${index} clicked`);
            player.playTrack(index);
        });
    });
    
    // Keyboard shortcuts
    // Ignora quando o foco está em campos de texto
    document.addEventListener('keydown', (e) => {
        const tag = document.activeElement?.tagName?.toLowerCase();
        if (tag === 'input' || tag === 'textarea' || tag === 'select') return;

        switch(e.key) {
            case ' ':
                e.preventDefault();
                player.togglePlay();
                break;
            case 'ArrowLeft':
                e.preventDefault();
                player.playPrevious();
                break;
            case 'ArrowRight':
                e.preventDefault();
                player.playNext();
                break;
        }
    });
    
    console.log('Controles do rádio inicializados!');
    console.log('Atalhos de teclado:');
    console.log('- Espaço: Play/Pause');
    console.log('- Seta esquerda: Anterior');
    console.log('- Seta direita: Próxima');
}

/**
 * Adiciona feedback visual ao clicar nos botões
 */
function addButtonFeedback(button, action) {
    button.style.transform = 'scale(0.9)';
    
    setTimeout(() => {
        button.style.transform = 'scale(1)';
    }, 150);
    
    // Som de clique (opcional)
    playClickSound();
}

/**
 * Toca som de clique mecânico (opcional)
 */
function playClickSound() {
    // Você pode adicionar um arquivo de som de clique mecânico aqui
    // const clickAudio = new Audio('assets/click.mp3');
    // clickAudio.volume = 0.3;
    // clickAudio.play();
}

/**
 * Mostra tooltip ao passar o mouse sobre os botões
 */
function initTooltips() {
    const buttons = document.querySelectorAll('.hotspot');
    
    buttons.forEach(button => {
        button.addEventListener('mouseenter', (e) => {
            const tooltip = e.target.getAttribute('title');
            if (tooltip) {
                // Você pode criar tooltips customizados aqui se quiser
                console.log('Tooltip:', tooltip);
            }
        });
    });
}

// Inicializar tooltips
initTooltips();

/**
 * Detecta inatividade e exibe dica
 */
let inactivityTimer;
function resetInactivityTimer() {
    clearTimeout(inactivityTimer);
    
    inactivityTimer = setTimeout(() => {
        if (!player.isOn) {
            player.showStatus('💡 Dica: Clique no primeiro botão para começar');
        }
    }, 30000); // 30 segundos
}

// Reset timer em qualquer interação
document.addEventListener('click', resetInactivityTimer);
document.addEventListener('keypress', resetInactivityTimer);
resetInactivityTimer();
