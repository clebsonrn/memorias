/**
 * Sistema de Mensagens - Memórias de um Pé Vermelho
 * reCAPTCHA v3 + feed paginado + validação frontend
 */

const Messages = (() => {

    const SITE_KEY   = '6Lc5im0sAAAAAHBy6MkTM_LbAGBvxucIg3SPVWIR';
    const API_URL    = 'api/messages.php';
    const MAX_CHARS  = 280;
    const MIN_CHARS  = 10;

    let currentPage  = 1;
    let totalPages   = 1;
    let isSubmitting = false;

    // --------------------------------------------------------
    // INIT
    // --------------------------------------------------------

    function init() {
        const form = document.getElementById('messageForm');
        if (!form) return;

        // Máscara automática no WhatsApp
        const whatsappInput = document.getElementById('senderWhatsapp');
        whatsappInput.addEventListener('input', () => {
            let v = whatsappInput.value.replace(/\D/g, '').slice(0, 11);
            if (v.length > 6) {
                v = `(${v.slice(0,2)}) ${v.slice(2,7)}-${v.slice(7)}`;
            } else if (v.length > 2) {
                v = `(${v.slice(0,2)}) ${v.slice(2)}`;
            } else if (v.length > 0) {
                v = `(${v}`;
            }
            whatsappInput.value = v;
        });

        // Contador de caracteres
        const textarea = document.getElementById('messageText');
        const counter  = document.getElementById('charCounter');

        textarea.addEventListener('input', () => {
            const len = textarea.value.length;
            counter.textContent = `${len}/${MAX_CHARS}`;
            counter.classList.toggle('warning', len > MAX_CHARS * 0.85);
            counter.classList.toggle('error',   len >= MAX_CHARS);
        });

        // Submit
        form.addEventListener('submit', handleSubmit);

        // Paginação
        document.getElementById('btnPrev')?.addEventListener('click', () => loadMessages(currentPage - 1));
        document.getElementById('btnNext')?.addEventListener('click', () => loadMessages(currentPage + 1));

        // Carrega mensagens ao entrar
        loadMessages(1);
    }

    // --------------------------------------------------------
    // SUBMIT
    // --------------------------------------------------------

    async function handleSubmit(e) {
        e.preventDefault();
        if (isSubmitting) return;

        const name      = document.getElementById('senderName').value.trim();
        const email     = document.getElementById('senderEmail').value.trim();
        const whatsapp  = document.getElementById('senderWhatsapp').value.trim();
        const message   = document.getElementById('messageText').value.trim();
        const consent   = document.getElementById('consentLgpd').checked;

        // Validações
        if (!name) return showFormError('Por favor, informe seu nome.');
        if (message.length < MIN_CHARS) return showFormError(`Mensagem muito curta. Mínimo ${MIN_CHARS} caracteres.`);
        if (message.length > MAX_CHARS) return showFormError(`Mensagem muito longa. Máximo ${MAX_CHARS} caracteres.`);

        // Validação de email (se preenchido)
        if (email && !validateEmail(email)) {
            return showFormError('E-mail inválido. Verifique o formato.');
        }

        // Validação de WhatsApp (se preenchido)
        const whatsappClean = cleanWhatsapp(whatsapp);
        if (whatsapp && !whatsappClean) {
            return showFormError('WhatsApp inválido. Use: (11) 99999-9999');
        }

        isSubmitting = true;
        setButtonState('loading');

        try {
            const token = await getRecaptchaToken();

            const response = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name,
                    email,
                    whatsapp:  whatsappClean,
                    message,
                    consent,
                    recaptcha_token: token,
                    website: '',
                }),
            });

            const data = await response.json();

            if (data.success) {
                showFormSuccess('Mensagem enviada! Obrigado por compartilhar. 🌾');
                document.getElementById('messageForm').reset();
                document.getElementById('charCounter').textContent = `0/${MAX_CHARS}`;
                loadMessages(1); // Recarrega feed do início
            } else {
                showFormError(data.message || 'Erro ao enviar. Tente novamente.');
            }

        } catch (err) {
            console.error('Erro ao enviar mensagem:', err);
            showFormError('Erro de conexão. Verifique sua internet.');
        } finally {
            isSubmitting = false;
            setButtonState('idle');
        }
    }

    // --------------------------------------------------------
    // VALIDAÇÕES
    // --------------------------------------------------------

    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email);
    }

    function cleanWhatsapp(raw) {
        // Remove tudo que não for dígito
        const digits = raw.replace(/\D/g, '');
        // Aceita 10 (fixo) ou 11 dígitos (celular), com ou sem 55
        if (digits.length === 13 && digits.startsWith('55')) return digits.slice(2); // +55 + 11 dígitos
        if (digits.length === 12 && digits.startsWith('55')) return digits.slice(2); // +55 + 10 dígitos
        if (digits.length === 11) return digits; // DDD + 9 dígitos
        if (digits.length === 10) return digits; // DDD + 8 dígitos (fixo)
        return ''; // inválido
    }

    // --------------------------------------------------------
    // RECAPTCHA
    // --------------------------------------------------------

    async function getRecaptchaToken() {
        await new Promise(resolve => grecaptcha.ready(resolve));
        return grecaptcha.execute(SITE_KEY, { action: 'submit' });
    }

    // --------------------------------------------------------
    // CARREGAR MENSAGENS
    // --------------------------------------------------------

    async function loadMessages(page = 1) {
        const feed = document.getElementById('messagesFeed');
        const paginationEl = document.getElementById('pagination');
        if (!feed) return;

        feed.innerHTML = '<div class="messages-loading">🌾 Carregando...</div>';

        try {
            const res  = await fetch(`${API_URL}?page=${page}`);
            const data = await res.json();

            if (!data.success) throw new Error('Falha ao carregar');

            currentPage = data.page;
            totalPages  = data.pages;

            // Atualiza contador total
            const totalEl = document.getElementById('messagesTotal');
            if (totalEl) {
                totalEl.textContent = data.total === 1
                    ? '1 mensagem'
                    : `${data.total} mensagens`;
            }

            // Renderiza mensagens
            if (data.messages.length === 0) {
                feed.innerHTML = `
                    <div class="messages-empty">
                        <p>🌾 Seja o primeiro a compartilhar sua memória!</p>
                    </div>`;
            } else {
                feed.innerHTML = data.messages.map(renderCard).join('');
            }

            // Paginação
            updatePagination(data.page, data.pages, data.total);

            // Scroll suave para o feed após trocar página
            if (page > 1) {
                feed.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

        } catch (err) {
            console.error('Erro ao carregar mensagens:', err);
            feed.innerHTML = '<div class="messages-empty"><p>Erro ao carregar mensagens.</p></div>';
        }
    }

    // --------------------------------------------------------
    // RENDERIZAR CARD
    // --------------------------------------------------------

    function renderCard(msg) {
        const date = formatDate(msg.date);
        // Sanitiza para exibição
        const name    = escapeHTML(msg.name);
        const message = escapeHTML(msg.message);

        let html = `
            <div class="message-card">
                <p class="message-text">"${message}"</p>
                <p class="message-author">
                    <span class="author-icon">🌾</span>
                    <strong>${name}</strong>
                    <span class="message-date">${date}</span>
                </p>`;
        
        // Adiciona resposta do artista se houver
        if (msg.reply && msg.reply.text) {
            const replyText = escapeHTML(msg.reply.text).replace(/\n/g, '<br>');
            const replyDate = formatDate(msg.reply.date);
            
            html += `
                <div class="message-reply">
                    <div class="reply-header">
                        <span class="reply-author-icon">🎵</span>
                        <span class="reply-author">Clebson Ribeiro</span>
                    </div>
                    <p class="reply-text">${replyText}</p>
                    <div class="reply-date">${replyDate}</div>
                </div>`;
        }
        
        html += `</div>`;
        return html;
    }

    // --------------------------------------------------------
    // PAGINAÇÃO
    // --------------------------------------------------------

    function updatePagination(page, pages, total) {
        const paginationEl = document.getElementById('pagination');
        if (!paginationEl || pages <= 1) {
            if (paginationEl) paginationEl.style.display = 'none';
            return;
        }

        paginationEl.style.display = 'flex';

        const btnPrev = document.getElementById('btnPrev');
        const btnNext = document.getElementById('btnNext');
        const pageInfo = document.getElementById('pageInfo');

        btnPrev.disabled = (page <= 1);
        btnNext.disabled = (page >= pages);
        pageInfo.textContent = `${page} de ${pages}`;
    }

    // --------------------------------------------------------
    // HELPERS UI
    // --------------------------------------------------------

    function setButtonState(state) {
        const btn = document.getElementById('submitBtn');
        if (!btn) return;
        if (state === 'loading') {
            btn.disabled = true;
            btn.textContent = 'Enviando...';
        } else {
            btn.disabled = false;
            btn.textContent = 'Enviar Mensagem 🌾';
        }
    }

    function showFormError(msg) {
        const el = document.getElementById('formFeedback');
        if (!el) return;
        el.textContent = msg;
        el.className = 'form-feedback error';
        el.style.display = 'block';
        setTimeout(() => { el.style.display = 'none'; }, 5000);
    }

    function showFormSuccess(msg) {
        const el = document.getElementById('formFeedback');
        if (!el) return;
        el.textContent = msg;
        el.className = 'form-feedback success';
        el.style.display = 'block';
        setTimeout(() => { el.style.display = 'none'; }, 6000);
    }

    function formatDate(iso) {
        try {
            const d = new Date(iso);
            const now = new Date();
            const diff = Math.floor((now - d) / 1000);

            if (diff < 60)     return 'agora mesmo';
            if (diff < 3600)   return `há ${Math.floor(diff / 60)} min`;
            if (diff < 86400)  return `há ${Math.floor(diff / 3600)}h`;
            if (diff < 604800) return `há ${Math.floor(diff / 86400)} dias`;

            return d.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' });
        } catch {
            return '';
        }
    }

    function escapeHTML(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // --------------------------------------------------------
    // PUBLIC
    // --------------------------------------------------------

    return { init, loadMessages };

})();

// Inicia quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => Messages.init());
