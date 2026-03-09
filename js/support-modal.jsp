/**
 * Support Modal - Memórias de um Pé Vermelho
 * Sistema de apoio ao artista
 * v1.0
 */

class SupportModal {
    constructor() {
        this.modal = null;
        this.selectedAmount = null;
        this.totalSupporters = 0;
        this.isOpen = false;
        this.mpLoaded = false;
        this.mp = null;
        
        this.init();
    }
    
    /**
     * Inicializa o modal
     */
    init() {
        // Cria estrutura do modal
        this.createModal();
        
        // Cria botão fixo
        this.createFixedButton();
        
        // Event listeners
        this.attachEventListeners();
        
        // Busca contador de apoiadores
        this.fetchSupportersCount();
        
        // Carrega SDK do Mercado Pago
        this.loadMercadoPagoSDK();
        
        console.log('💛 Support Modal inicializado');
    }
    
    /**
     * Carrega SDK do Mercado Pago
     */
    loadMercadoPagoSDK() {
        if (this.mpLoaded) return;
        
        const script = document.createElement('script');
        script.src = 'https://sdk.mercadopago.com/js/v2';
        script.onload = () => {
            console.log('✅ SDK Mercado Pago carregado');
            this.mpLoaded = true;
        };
        script.onerror = () => {
            console.error('❌ Erro ao carregar SDK do Mercado Pago');
        };
        document.head.appendChild(script);
    }
    
    /**
     * Cria estrutura HTML do modal
     */
    createModal() {
        const modalHTML = `
            <div class="support-modal" id="supportModal">
                <div class="support-overlay"></div>
                <div class="support-content">
                    <button class="support-close" id="supportClose">✕</button>
                    
                    <div class="support-greeting">Ô de casa...</div>
                    
                    <div class="support-stats">
                        <span class="emoji">💛</span>
                        <span id="supportersText">Carregando...</span>
                    </div>
                    
                    <div class="support-value">
                        No Spotify, eu receberia <strong>R$ 0,004</strong> por audição.<br>
                        Aqui, você decide quanto vale manter essa cultura viva.
                    </div>
                    
                    <div class="support-position" id="supportPosition">
                        <span class="emoji">🌾</span>
                        Você será o apoiador <strong>#<span id="nextPosition">1</span></strong>
                    </div>
                    
                    <div class="support-amounts">
                        <button class="amount-btn" data-amount="1">
                            <span class="amount-value">R$ 1</span>
                            <span class="amount-label">Um cafezinho</span>
                        </button>
                        <button class="amount-btn" data-amount="5">
                            <span class="amount-value">R$ 5</span>
                            <span class="amount-label">Recomendado ⭐</span>
                        </button>
                        <button class="amount-btn" data-amount="10">
                            <span class="amount-value">R$ 10</span>
                            <span class="amount-label">Generoso!</span>
                        </button>
                        <button class="amount-btn custom" data-amount="custom">
                            <span class="amount-value">Outro valor</span>
                            <span class="amount-label">Você decide</span>
                        </button>
                    </div>
                    
                    <div class="custom-amount-input" id="customAmountInput">
                        <input 
                            type="number" 
                            id="customAmount" 
                            placeholder="R$ 0,00" 
                            min="1" 
                            max="1000"
                            step="0.01"
                        >
                    </div>
                    
                    <!-- Placeholder para Mercado Pago -->
                    <div class="mercadopago-container" id="mercadopagoContainer">
                        <!-- SDK do Mercado Pago será carregado aqui -->
                    </div>
                    
                    <div class="support-loading" id="supportLoading">
                        <div class="spinner"></div>
                        <p>Preparando pagamento...</p>
                    </div>
                    
                    <div class="support-actions">
                        <button class="btn-support-primary" id="btnContinue" disabled>
                            💛 Continuar
                        </button>
                        <button class="btn-support-later" id="btnLater">
                            Depois
                        </button>
                    </div>
                    
                    <p class="support-optional">
                        💛 Totalmente opcional. Gratidão é suficiente!
                    </p>
                </div>
            </div>
        `;
        
        // Adiciona ao body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.modal = document.getElementById('supportModal');
    }
    
    /**
     * Cria botão fixo no canto da tela
     */
    createFixedButton() {
        const buttonHTML = `
            <button class="support-fixed-btn" id="supportFixedBtn">
                <span class="emoji">💛</span>
                Apoiar o artista
            </button>
        `;
        
        document.body.insertAdjacentHTML('beforeend', buttonHTML);
        
        // Event listener
        document.getElementById('supportFixedBtn').addEventListener('click', () => {
            this.open();
        });
    }
    
    /**
     * Adiciona event listeners
     */
    attachEventListeners() {
        // Fechar modal
        document.getElementById('supportClose').addEventListener('click', () => {
            this.close();
        });
        
        // Fechar ao clicar no overlay
        this.modal.querySelector('.support-overlay').addEventListener('click', () => {
            this.close();
        });
        
        // Fechar com ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.close();
            }
        });
        
        // Botão "Depois"
        document.getElementById('btnLater').addEventListener('click', () => {
            this.close();
        });
        
        // Seleção de valores
        const amountBtns = document.querySelectorAll('.amount-btn');
        amountBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.selectAmount(e.currentTarget);
            });
        });
        
        // Input customizado
        const customInput = document.getElementById('customAmount');
        customInput.addEventListener('input', (e) => {
            const value = parseFloat(e.target.value);
            if (value >= 1 && value <= 1000) {
                this.selectedAmount = value;
                this.updateContinueButton();
            }
        });
        
        // Botão Continuar
        document.getElementById('btnContinue').addEventListener('click', () => {
            this.processDonation();
        });
    }
    
    /**
     * Busca contador de apoiadores
     */
    async fetchSupportersCount() {
        try {
            const response = await fetch('/api/get-supporters-count.php');
            const data = await response.json();
            
            if (data.success) {
                this.totalSupporters = data.total || 0;
            } else {
                this.totalSupporters = 0;
            }
            
            this.updateSupportersText();
        } catch (error) {
            console.error('Erro ao buscar apoiadores:', error);
            this.totalSupporters = 0;
            this.updateSupportersText();
        }
    }
    
    /**
     * Atualiza texto de apoiadores
     */
    updateSupportersText() {
        const textEl = document.getElementById('supportersText');
        const positionEl = document.getElementById('nextPosition');
        
        if (this.totalSupporters === 0) {
            textEl.textContent = 'Seja o primeiro a valorizar a viola caipira brasileira';
        } else if (this.totalSupporters === 1) {
            textEl.textContent = '1 pessoa já entendeu o valor da viola caipira';
        } else {
            textEl.textContent = `${this.totalSupporters} pessoas já entenderam o valor da viola caipira`;
        }
        
        positionEl.textContent = this.totalSupporters + 1;
    }
    
    /**
     * Seleciona valor da doação
     */
    selectAmount(btn) {
        // Remove seleção anterior
        document.querySelectorAll('.amount-btn').forEach(b => {
            b.classList.remove('selected');
        });
        
        // Adiciona seleção
        btn.classList.add('selected');
        
        const amount = btn.dataset.amount;
        const customInput = document.getElementById('customAmountInput');
        
        if (amount === 'custom') {
            // Mostra input customizado
            customInput.classList.add('show');
            document.getElementById('customAmount').focus();
            this.selectedAmount = null;
        } else {
            // Valor pré-definido
            customInput.classList.remove('show');
            this.selectedAmount = parseFloat(amount);
        }
        
        this.updateContinueButton();
    }
    
    /**
     * Atualiza estado do botão Continuar
     */
    updateContinueButton() {
        const btn = document.getElementById('btnContinue');
        
        if (this.selectedAmount && this.selectedAmount >= 1) {
            btn.disabled = false;
            btn.textContent = `💛 Apoiar com R$ ${this.selectedAmount.toFixed(2)}`;
        } else {
            btn.disabled = true;
            btn.textContent = '💛 Continuar';
        }
    }
    
    /**
     * Processa doação com iFrame
     */
    async processDonation() {
        if (!this.selectedAmount || this.selectedAmount < 1) {
            alert('Selecione um valor para continuar');
            return;
        }
        
        console.log('💰 Processando doação:', this.selectedAmount);
        
        const loading = document.getElementById('supportLoading');
        loading.classList.add('show');
        
        try {
            // Pega fingerprint do analytics
            const fingerprint = localStorage.getItem('memorias_user_id') || null;
            
            // Chama API para criar pagamento
            const response = await fetch('/api/create-payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    amount: this.selectedAmount,
                    email: null, // TODO: Coletar email (opcional)
                    fingerprint: fingerprint
                })
            });
            
            const data = await response.json();
            
            loading.classList.remove('show');
            
            if (!data.success) {
                alert('Erro ao criar pagamento: ' + (data.error || 'Tente novamente'));
                return;
            }
            
            console.log('✅ Preferência criada:', data.preference_id);
            
            // Abre checkout no iFrame
            this.openCheckoutIframe(data.preference_id, data.public_key);
            
        } catch (error) {
            loading.classList.remove('show');
            console.error('Erro ao processar doação:', error);
            alert('Erro ao processar pagamento. Tente novamente.');
        }
    }
    
    /**
     * Abre checkout do Mercado Pago no iFrame
     */
    async openCheckoutIframe(preferenceId, publicKey) {
        // Aguarda SDK carregar
        if (!this.mpLoaded) {
            console.log('⏳ Aguardando SDK carregar...');
            await new Promise(resolve => {
                const interval = setInterval(() => {
                    if (this.mpLoaded && window.MercadoPago) {
                        clearInterval(interval);
                        resolve();
                    }
                }, 100);
            });
        }
        
        // Inicializa Mercado Pago
        if (!this.mp) {
            this.mp = new window.MercadoPago(publicKey);
        }
        
        // Esconde seleção de valores
        const content = this.modal.querySelector('.support-content');
        content.classList.add('payment-active');
        
        // Mostra container do checkout
        const container = document.getElementById('mercadopagoContainer');
        container.classList.add('show');
        
        // Cria checkout no iFrame
        try {
            const checkout = this.mp.checkout({
                preference: {
                    id: preferenceId
                },
                render: {
                    container: '#mercadopagoContainer',
                    label: 'Pagar'
                },
                autoOpen: true
            });
            
            console.log('✅ Checkout iFrame aberto');
            
        } catch (error) {
            console.error('Erro ao abrir checkout:', error);
            
            // Fallback: abre em nova aba
            const checkoutUrl = `https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=${preferenceId}`;
            window.open(checkoutUrl, '_blank');
            
            setTimeout(() => {
                this.close();
                alert('✅ Abrimos o checkout do Mercado Pago em nova aba!\n\nObrigado pelo seu apoio! 💛');
            }, 500);
        }
    }
    
    /**
     * Abre modal
     */
    open(reason = 'manual') {
        if (this.isOpen) return;
        
        console.log('💛 Abrindo modal:', reason);
        
        this.modal.classList.add('show');
        this.isOpen = true;
        
        // Atualiza contador
        this.fetchSupportersCount();
        
        // Impede scroll da página
        document.body.style.overflow = 'hidden';
        
        // Analytics
        if (window.listeningAnalytics) {
            // Marca que viu o modal (para não repetir)
            window.listeningAnalytics.markSupportModalSeen(reason);
        }
    }
    
    /**
     * Fecha modal
     */
    close() {
        if (!this.isOpen) return;
        
        console.log('💛 Fechando modal');
        
        this.modal.classList.remove('show');
        this.isOpen = false;
        
        // Restaura scroll
        document.body.style.overflow = '';
        
        // Limpa iFrame se estava aberto
        const container = document.getElementById('mercadopagoContainer');
        if (container) {
            container.innerHTML = '';
            container.classList.remove('show');
        }
        
        // Remove classe payment-active
        const content = this.modal.querySelector('.support-content');
        if (content) {
            content.classList.remove('payment-active');
        }
        
        // Reset
        this.selectedAmount = null;
        document.querySelectorAll('.amount-btn').forEach(b => {
            b.classList.remove('selected');
        });
        document.getElementById('customAmountInput').classList.remove('show');
        document.getElementById('customAmount').value = '';
        this.updateContinueButton();
    }
}

// ============================================
// INTEGRAÇÃO COM ANALYTICS
// ============================================

/**
 * Verifica se deve mostrar modal automaticamente
 */
function checkAutoShowSupportModal() {
    if (!window.listeningAnalytics) {
        console.log('⚠️ Analytics não carregado');
        return;
    }
    
    const reason = window.listeningAnalytics.shouldShowSupportModal();
    
    if (reason) {
        console.log('🎯 Gatilho ativado:', reason);
        
        // Aguarda 2 segundos após o gatilho
        setTimeout(() => {
            if (window.supportModal) {
                window.supportModal.open(reason);
            }
        }, 2000);
    }
}

// ============================================
// INICIALIZAÇÃO
// ============================================

// Aguarda DOM carregar
document.addEventListener('DOMContentLoaded', () => {
    // Inicializa modal
    window.supportModal = new SupportModal();
    
    console.log('✅ Support Modal pronto');
});

// Escuta evento de álbum completo
window.addEventListener('albumCompleted', (event) => {
    console.log('🎉 Álbum completo!', event.detail);
    checkAutoShowSupportModal();
});

// Expõe função global para abrir modal
window.openSupportModal = function() {
    if (window.supportModal) {
        window.supportModal.open('manual');
    }
};

window.closeSupportModal = function() {
    if (window.supportModal) {
        window.supportModal.close();
    }
};
