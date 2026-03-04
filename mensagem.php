<?php
/**
 * Página Individual de Mensagem
 * URL: /mensagem/{id}
 * v3 - Badge de apoiador
 */

// Configurações
define('MESSAGES_FILE', __DIR__ . '/api/messages.json');
define('SITE_URL', 'https://memorias.clebsonribeiro.com.br');
define('SITE_NAME', 'Viola Caipira - Clebson Ribeiro');
define('ARTIST_NAME', 'Clebson Ribeiro');

// Pega ID da URL
$requestUri = $_SERVER['REQUEST_URI'];
preg_match('/\/mensagem\/([a-zA-Z0-9_\.]+)/', $requestUri, $matches);
$messageId = $matches[1] ?? null;

if (!$messageId) {
    http_response_code(404);
    die('Mensagem não encontrada');
}

// Carrega mensagens
$messages = json_decode(@file_get_contents(MESSAGES_FILE), true) ?? [];

// Busca mensagem
$message = null;
foreach ($messages as $m) {
    if ($m['id'] === $messageId) {
        $message = $m;
        break;
    }
}

if (!$message) {
    http_response_code(404);
    die('Mensagem não encontrada');
}

// Prepara dados para SEO
$userName    = htmlspecialchars($message['name'] ?? 'Anônimo');
$messageText = htmlspecialchars($message['message'] ?? '');
$messageDate = !empty($message['date']) ? date('d/m/Y', strtotime($message['date'])) : '';
$hasReply    = !empty($message['reply']);

// 🆕 Badge de apoiador
$isSupporter       = !empty($message['is_supporter']);
$supporterPosition = (int)($message['supporter_position'] ?? 0);
$supporterSince    = !empty($message['supporter_since'])
    ? date('d/m/Y', strtotime($message['supporter_since']))
    : '';

// Meta tags
$pageTitle       = "Mensagem de {$userName} - " . SITE_NAME;
$pageDescription = mb_substr($messageText, 0, 150) . ($hasReply ? ' • Com resposta de ' . ARTIST_NAME : '');
$pageUrl         = SITE_URL . '/mensagem/' . $messageId;
$currentUrl      = $pageUrl;
$ogImage         = SITE_URL . '/img/og-image.jpg';

// Schema.org V3
$schemaData = [
    '@context' => 'https://schema.org',
    '@type'    => 'CreativeWork',
    '@id'      => SITE_URL,
    'name'     => 'Memórias de um Pé Vermelho',
    'author'   => [
        '@type' => 'Person',
        'name'  => ARTIST_NAME,
        'url'   => 'https://clebsonribeiro.com.br'
    ],
    'comment' => [
        '@type'         => 'Comment',
        'url'           => $currentUrl,
        'author'        => ['@type' => 'Person', 'name' => $userName],
        'text'          => $messageText,
        'datePublished' => $message['date'] ?? date('c')
    ]
];

if ($hasReply) {
    $schemaData['comment'] = [
        [
            '@type'         => 'Comment',
            'url'           => $currentUrl,
            'author'        => ['@type' => 'Person', 'name' => $userName],
            'text'          => $messageText,
            'datePublished' => $message['date'] ?? date('c')
        ],
        [
            '@type'         => 'Comment',
            'author'        => [
                '@type' => 'Person',
                'name'  => ARTIST_NAME,
                'url'   => 'https://clebsonribeiro.com.br'
            ],
            'text'          => $message['reply']['text'],
            'datePublished' => $message['reply']['date']
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?= $pageTitle ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <link rel="canonical" href="<?= $pageUrl ?>">
    
    <meta property="og:type"        content="article">
    <meta property="og:title"       content="<?= $pageTitle ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta property="og:url"         content="<?= $pageUrl ?>">
    <meta property="og:image"       content="<?= $ogImage ?>">
    <meta property="og:site_name"   content="<?= SITE_NAME ?>">
    
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="<?= $pageTitle ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta name="twitter:image"       content="<?= $ogImage ?>">
    
    <script type="application/ld+json">
    <?= json_encode($schemaData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
    </script>
    
    <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    
    body {
        background: #0a0a0a;
        color: #d4c5b0;
        font-family: Georgia, 'Times New Roman', serif;
        min-height: 100vh;
        padding: 40px 20px;
    }
    
    .container { max-width: 800px; margin: 0 auto; }
    
    .back-link {
        display: inline-block;
        color: #c9a030;
        text-decoration: none;
        margin-bottom: 30px;
        font-size: 0.9rem;
        transition: color 0.3s;
    }
    .back-link:hover { color: #ff7820; }
    
    .message-card {
        background: rgba(20, 15, 10, 0.9);
        border: 1px solid rgba(200, 170, 120, 0.2);
        border-radius: 15px;
        padding: 40px;
        margin-bottom: 30px;
    }

    /* 🆕 Card especial para apoiadores */
    .message-card--supporter {
        border-color: rgba(212, 160, 23, 0.4);
        border-left: 4px solid #D4A017;
    }
    
    .message-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid rgba(200, 170, 120, 0.15);
    }
    
    .message-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #ff7820, #ffa040);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        flex-shrink: 0;
    }

    /* 🆕 Avatar dourado para apoiadores */
    .message-avatar--supporter {
        background: linear-gradient(135deg, #8B4513, #D4A017);
    }
    
    .message-meta h1 {
        font-size: 1.4rem;
        color: #f4e4c1;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .message-date { font-size: 0.85rem; color: #8a7a6a; }

    /* 🆕 Badge de apoiador */
    .supporter-badge {
        display: inline-flex;
        align-items: center;
        background: linear-gradient(135deg, #8B4513, #A0522D);
        color: #FFF8DC;
        font-size: 0.7rem;
        font-weight: bold;
        padding: 3px 10px;
        border-radius: 12px;
        letter-spacing: 0.3px;
        box-shadow: 0 2px 6px rgba(139, 69, 19, 0.35);
        white-space: nowrap;
    }

    /* 🆕 Faixa "apoiador desde" */
    .supporter-since {
        font-size: 0.78rem;
        color: #c9a030;
        margin-top: 4px;
    }
    
    .message-text {
        font-size: 1.1rem;
        line-height: 1.8;
        color: #d4c5b0;
        font-style: italic;
        margin-bottom: 30px;
    }
    
    .reply-section {
        background: rgba(255, 120, 30, 0.05);
        border-left: 4px solid #ff7820;
        border-radius: 10px;
        padding: 30px;
        margin-top: 30px;
    }
    
    .reply-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        color: #ff7820;
        font-weight: bold;
    }
    
    .reply-text   { font-size: 1.05rem; line-height: 1.7; color: #f4e4c1; }
    .reply-date   { margin-top: 15px; font-size: 0.8rem; color: #8a7a6a; }
    
    .cta-section  { text-align: center; padding: 40px 20px; }
    .cta-title    { font-size: 1.8rem; color: #f4e4c1; margin-bottom: 15px; }
    .cta-subtitle { font-size: 1rem; color: #8a7a6a; margin-bottom: 30px; }
    
    .btn-primary {
        display: inline-block;
        background: linear-gradient(135deg, #ff7820, #ffa040);
        color: #fff;
        text-decoration: none;
        padding: 15px 40px;
        border-radius: 30px;
        font-weight: bold;
        font-size: 1.05rem;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(255, 120, 30, 0.3);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 25px rgba(255, 120, 30, 0.5);
    }
    
    @media (max-width: 600px) {
        body { padding: 20px 15px; }
        .message-card { padding: 25px 20px; }
        .message-header { flex-direction: column; text-align: center; }
        .message-meta h1 { font-size: 1.2rem; justify-content: center; }
        .message-text { font-size: 1rem; }
        .supporter-badge { font-size: 0.65rem; }
    }
    </style>
</head>
<body>
    <div class="container">
        <a href="/" class="back-link">← Voltar para o álbum</a>
        
        <article class="message-card <?= $isSupporter ? 'message-card--supporter' : '' ?>">
            <header class="message-header">

                <!-- 🆕 Avatar dourado para apoiadores -->
                <div class="message-avatar <?= $isSupporter ? 'message-avatar--supporter' : '' ?>">
                    <?= $isSupporter ? '💛' : '🌾' ?>
                </div>

                <div class="message-meta">
                    <h1>
                        <?= $userName ?>
                        <?php if ($isSupporter): ?>
                            <span class="supporter-badge">
                                💛 APOIADOR #<?= $supporterPosition ?>
                            </span>
                        <?php endif; ?>
                    </h1>

                    <?php if ($messageDate): ?>
                        <div class="message-date">📅 <?= $messageDate ?></div>
                    <?php endif; ?>

                    <!-- 🆕 "Apoiador desde" -->
                    <?php if ($isSupporter && $supporterSince): ?>
                        <div class="supporter-since">
                            ✅ Apoiou em <?= $supporterSince ?>
                        </div>
                    <?php endif; ?>
                </div>
            </header>
            
            <div class="message-text">
                "<?= nl2br($messageText) ?>"
            </div>
            
            <?php if ($hasReply): ?>
            <aside class="reply-section">
                <div class="reply-header">
                    <span>🎵</span>
                    <span>Resposta de <?= ARTIST_NAME ?></span>
                </div>
                <div class="reply-text">
                    <?= nl2br(htmlspecialchars($message['reply']['text'])) ?>
                </div>
                <?php if (!empty($message['reply']['date'])): ?>
                <div class="reply-date">
                    <?= date('d/m/Y', strtotime($message['reply']['date'])) ?>
                </div>
                <?php endif; ?>
            </aside>
            <?php endif; ?>
        </article>
        
        <section class="cta-section">
            <h2 class="cta-title">Ouça o Álbum Completo</h2>
            <p class="cta-subtitle">10 clássicos da música sertaneja na viola caipira</p>
            <a href="/" class="btn-primary">🎧 Ouvir Agora</a>
        </section>
    </div>
</body>
</html>