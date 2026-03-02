<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memórias de um Pé Vermelho - Clebson Ribeiro</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="192x192" href="https://clebsonribeiro.com.br/wp-content/uploads/2023/10/cropped-icon1-1-192x192.png">
    <link rel="apple-touch-icon" href="https://clebsonribeiro.com.br/wp-content/uploads/2023/10/cropped-icon1-1-192x192.png">

    <!-- reCAPTCHA v3 -->
    <script src="https://www.google.com/recaptcha/api.js?render=6Lc5im0sAAAAAHBy6MkTM_LbAGBvxucIg3SPVWIR"></script>

    <link rel="stylesheet" href="css/style.css?v=9">
</head>
<body>
    <div class="container">
        <div class="background-overlay"></div>

        <!-- Welcome Overlay -->
        <div class="welcome-overlay" id="welcomeOverlay">
            <div class="welcome-content">
                <h2>🎵 Bem-vindo ao EPK</h2>
                <h3>Memórias de um Pé Vermelho</h3>
                <p>Uma experiência musical imersiva com rádio vintage</p>
                <div class="instructions">
                    <div class="instruction-item">
                        <span class="instruction-icon">🔘</span>
                        <p><strong>1º botão</strong> - Liga/Desliga o rádio</p>
                    </div>
                    <div class="instruction-item">
                        <span class="instruction-icon">▶️</span>
                        <p><strong>2º botão</strong> - Play/Pause</p>
                    </div>
                    <div class="instruction-item">
                        <span class="instruction-icon">⏮️</span>
                        <p><strong>3º botão</strong> - Música anterior</p>
                    </div>
                    <div class="instruction-item">
                        <span class="instruction-icon">⏭️</span>
                        <p><strong>4º botão</strong> - Próxima música</p>
                    </div>
                </div>
                <button class="start-button" id="startButton">🎵 Tocar Agora</button>
                <p class="tip">💡 Use as setas do teclado para navegar entre as músicas</p>
            </div>
        </div>

        <!-- Main content -->
        <div class="content">
            <header class="header">
                <h1 class="album-title">Memórias de um pé vermelho</h1>
                <h2 class="artist-name">Clebson Ribeiro</h2>
            </header>

            <!-- Radio Interface -->
            <div class="radio-container">
                <div class="radio-wrapper">
                    <img src="assets/radio.png" alt="Rádio Vintage" class="radio-image" id="radioImage">
                    <div class="flame-overlay"></div>
                    <div class="radio-controls">
                        <button class="hotspot power-btn" id="powerBtn" title="Ligar/Desligar">
                            <span class="power-hint" id="powerHint">
                                <span class="hint-text">Clique aqui para começar</span>
                                <span class="hint-arrow">👆</span>
                            </span>
                        </button>
                        <button class="hotspot play-btn"  id="playBtn"  title="Play/Pause"></button>
                        <button class="hotspot prev-btn"  id="prevBtn"  title="Anterior"></button>
                        <button class="hotspot next-btn"  id="nextBtn"  title="Próxima"></button>
                    </div>
                    <div class="radio-status" id="radioStatus">
                        <span class="status-text">Clique no primeiro botão para ligar o rádio</span>
                    </div>
                </div>

                <div class="now-playing" id="nowPlaying">
                    <div class="track-info">
                        <span class="track-number" id="trackNumber">-</span>
                        <span class="track-name"   id="trackName">Aguardando...</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                    <div class="time-display">
                        <span id="currentTime">0:00</span>
                        <span id="totalTime">0:00</span>
                    </div>
                </div>
            </div>

            <!-- ============================================
                 CARTA DO ARTISTA
                 ============================================ -->
            <section class="artist-letter">

                <div class="letter-photo-wrapper">
                    <div class="letter-photo-frame">
                        <img src="assets/clebson.jpeg" alt="Clebson Ribeiro" class="letter-photo">
                        <div class="photo-grain"></div>
                    </div>
                </div>

                <div class="letter-content">
                    <div class="letter-ornament">❧</div>

                    <p class="letter-text">
                        <span class="letter-dropcap">Ô</span> de casa…
                    </p>
                    <p class="letter-text">
                        Se achegue mais… puxe uma cadeira, que a prosa hoje é das boas.
                    </p>
                    <p class="letter-text">
                        Olha… é com alegria danada que eu recebo você aqui nessa pré-audição do álbum
                        <em>Memórias de um Pé Vermelho</em>.
                    </p>
                    <p class="letter-text">
                        Esse trabalho não é só um punhado de músicas, não. É retrato de infância.
                        É cheiro de cozinha acesa cedo. É voz de mãe cantando enquanto ajeitava
                        a vida dentro de casa.
                    </p>
                    <p class="letter-text">
                        São canções que eu ouvi ainda menino, lá no tempo em que o rádio era
                        companhia e a música era conselho.
                    </p>
                    <p class="letter-text">
                        E sabe o que tem de diferente nisso tudo? Mais de trinta anos de estrada,
                        tocando de tudo quanto é estilo, misturando vivência com lembrança…
                        tudo isso acabou se encontrando aqui, nesse disco.
                        <em>Como rio que deságua no mesmo mar.</em>
                    </p>
                    <p class="letter-text">
                        Eu te convido… de coração aberto… a embarcar nessa viagem comigo.
                    </p>
                    <p class="letter-text letter-highlight">
                        E deixo um pedido simples — coisa de músico independente:
                    </p>
                    <p class="letter-text">
                        A gente não ganha fortuna com audição, não. Ganha é gratidão.
                        Ganha o privilégio de ser ouvido. E ganha ainda mais quando você
                        conta pra gente o que sentiu.
                    </p>
                    <p class="letter-text">
                        Então escute… sinta… e depois me diga: <em>o que essa música despertou aí dentro?</em>
                    </p>

                    <p class="letter-signature">
                        Um grande abraço…<br>
                        <span>e que a viola nunca se cale.</span>
                    </p>

                    <div class="letter-ornament letter-ornament-bottom">✦</div>
                </div>

            </section>

            <!-- Tracklist -->
            <div class="tracklist-container">
                <h3>Faixas do Álbum</h3>
                <ol class="tracklist" id="tracklist">
                    <li data-track="0"><span class="track-title">Cabocla / Chico Mineiro / Nhambu Xita</span><span class="play-count" data-count-id="0">👂 <span class="count-number">0</span></span></li>
                    <li data-track="1"><span class="track-title">Romaria</span><span class="play-count" data-count-id="1">👂 <span class="count-number">0</span></span></li>
                    <li data-track="2"><span class="track-title">Tocando em Frente</span><span class="play-count" data-count-id="2">👂 <span class="count-number">0</span></span></li>
                    <li data-track="3"><span class="track-title">Amargurado</span><span class="play-count" data-count-id="3">👂 <span class="count-number">0</span></span></li>
                    <li data-track="4"><span class="track-title">Antonio e Natália</span><span class="play-count" data-count-id="4">👂 <span class="count-number">0</span></span></li>
                    <li data-track="5"><span class="track-title">Vaca Estrela e Boi Fubá</span><span class="play-count" data-count-id="5">👂 <span class="count-number">0</span></span></li>
                    <li data-track="6"><span class="track-title">Disparada</span><span class="play-count" data-count-id="6">👂 <span class="count-number">0</span></span></li>
                    <li data-track="7"><span class="track-title">Mercedita</span><span class="play-count" data-count-id="7">👂 <span class="count-number">0</span></span></li>
                    <li data-track="8"><span class="track-title">Vide Vida Marvada</span><span class="play-count" data-count-id="8">👂 <span class="count-number">0</span></span></li>
                    <li data-track="9"><span class="track-title">A Terra e o Tempo</span><span class="play-count" data-count-id="9">👂 <span class="count-number">0</span></span></li>
                </ol>
            </div>

            <!-- ============================================
                 SEÇÃO DE MENSAGENS
                 ============================================ -->
            <section class="messages-section">

                <!-- Formulário -->
                <div class="messages-form-wrapper">
                    <h3 class="messages-title">💬 Deixe sua mensagem</h3>
                    <p class="messages-subtitle">O que essa experiência, música ou artista significou para você?</p>

                    <form id="messageForm" class="message-form" novalidate>

                        <!-- Honeypot oculto — não toque -->
                        <input type="text" name="website" id="website" tabindex="-1"
                               style="position:absolute;left:-9999px;opacity:0;" autocomplete="off">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="senderName">Seu nome <span class="required">*</span></label>
                                <input type="text" id="senderName" name="name"
                                       placeholder="Como quer ser chamado?"
                                       maxlength="50" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="senderEmail">E-mail <span class="optional">opcional</span></label>
                                <input type="email" id="senderEmail" name="email"
                                       placeholder="seu@email.com"
                                       maxlength="100">
                            </div>
                            <div class="form-group">
                                <label for="senderWhatsapp">WhatsApp <span class="optional">opcional</span></label>
                                <input type="tel" id="senderWhatsapp" name="whatsapp"
                                       placeholder="(11) 99999-9999"
                                       maxlength="20">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="messageText">Mensagem <span class="required">*</span></label>
                            <textarea id="messageText" name="message"
                                      placeholder="Conte sua memória, fale sobre uma música, o artista..."
                                      maxlength="280" rows="4" required></textarea>
                            <span class="char-counter" id="charCounter">0/280</span>
                        </div>

                        <!-- Consentimento LGPD -->
                        <label class="consent-label">
                            <input type="checkbox" id="consentLgpd" name="consent" checked>
                            <span class="consent-text">
                                Aceito receber novidades de Clebson Ribeiro.<br>
                                <small>Seus dados não serão compartilhados com terceiros.</small>
                            </span>
                        </label>

                        <div id="formFeedback" class="form-feedback" style="display:none;"></div>

                        <button type="submit" id="submitBtn" class="submit-btn">
                            Enviar Mensagem 🌾
                        </button>

                        <p class="recaptcha-notice">
                            Protegido por reCAPTCHA —
                            <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Privacidade</a> &
                            <a href="https://policies.google.com/terms"   target="_blank" rel="noopener">Termos</a>
                        </p>
                    </form>
                </div>

                <!-- Feed de mensagens -->
                <div class="messages-feed-wrapper">
                    <div class="feed-header">
                        <h3 class="messages-title">🌾 O que o povo está dizendo</h3>
                        <span class="messages-total" id="messagesTotal"></span>
                    </div>

                    <div id="messagesFeed" class="messages-feed">
                        <div class="messages-loading">🌾 Carregando...</div>
                    </div>

                    <!-- Paginação -->
                    <div id="pagination" class="pagination" style="display:none;">
                        <button id="btnPrev" class="page-btn" disabled>← Anterior</button>
                        <span   id="pageInfo" class="page-info">1 de 1</span>
                        <button id="btnNext" class="page-btn">Próxima →</button>
                    </div>
                </div>

            </section>

            <footer class="footer">
                <p>&copy; 2026 Clebson Ribeiro — Todos os direitos reservados</p>
            </footer>
        </div>
    </div>

    <audio id="mainAudio"   preload="metadata"></audio>
    <audio id="effectAudio" preload="auto"></audio>


    <script src="js/audio-player.js?v=9"></script>
    <script src="js/radio-interface.js?v=9"></script>
    <script src="js/messages.js?v=9"></script>
</body>
</html>
