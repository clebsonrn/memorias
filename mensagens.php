<?php
/**
 * Feed Público de Mensagens
 * URL: /mensagens
 */

// Configurações
define('MESSAGES_FILE', __DIR__ . '/api/messages.json');
define('SITE_URL', 'https://memorias.clebsonribeiro.com.br');
define('SITE_NAME', 'Memórias de um Pé Vermelho - Viola Caipira - Clebson Ribeiro');
define('PER_PAGE', 10);

// Paginação
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Carrega mensagens
$allMessages = json_decode(@file_get_contents(MESSAGES_FILE), true) ?? [];
$allMessages = array_reverse($allMessages); // Mais recentes primeiro

// Filtra apenas mensagens públicas (sem dados privados)
$messages = array_map(function($m) {
    return [
        'id' => $m['id'],
        'name' => $m['name'] ?? 'Anônimo',
        'message' => $m['message'] ?? '',
        'date' => $m['date'] ?? '',
        'reply' => $m['reply'] ?? null
    ];
}, $allMessages);

// Paginação
$total = count($messages);
$totalPages = max(1, (int)ceil($total / PER_PAGE));
$page = min($page, $totalPages);
$offset = ($page - 1) * PER_PAGE;
$currentMessages = array_slice($messages, $offset, PER_PAGE);

// SEO
$pageTitle = $page > 1 
    ? "Mensagens - Página {$page} - " . SITE_NAME
    : "Mensagens do Público - " . SITE_NAME;
$pageDescription = "Veja as mensagens deixadas pelos ouvintes de Memórias de um Pé Vermelho. {$total} mensagens compartilhadas.";
$pageUrl = SITE_URL . '/mensagens' . ($page > 1 ? '/' . $page : '');
$canonicalUrl = SITE_URL . '/mensagens';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO -->
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <link rel="canonical" href="<?= $canonicalUrl ?>">
    
    <?php if ($page > 1): ?>
    <link rel="prev" href="<?= SITE_URL ?>/mensagens<?= $page > 2 ? '/' . ($page - 1) : '' ?>">
    <?php endif; ?>
    
    <?php if ($page < $totalPages): ?>
    <link rel="next" href="<?= SITE_URL ?>/mensagens/<?= $page + 1 ?>">
    <?php endif; ?>
    
    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta property="og:url" content="<?= $pageUrl ?>">
    <meta property="og:site_name" content="<?= SITE_NAME ?>">
    
    <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    
    body {
        background: #0a0a0a;
        color: #d4c5b0;
        font-family: Georgia, 'Times New Roman', serif;
        min-height: 100vh;
        padding: 40px 20px;
    }
    
    .container {
        max-width: 900px;
        margin: 0 auto;
    }
    
    .header {
        text-align: center;
        margin-bottom: 50px;
        padding-bottom: 30px;
        border-bottom: 2px solid rgba(200, 170, 120, 0.2);
    }
    
    .header h1 {
        font-size: 2.2rem;
        color: #f4e4c1;
        margin-bottom: 10px;
        font-family: 'Brush Script MT', cursive;
    }
    
    .header p {
        color: #8a7a6a;
        font-size: 1rem;
    }
    
    .back-link {
        display: inline-block;
        color: #c9a030;
        text-decoration: none;
        margin-bottom: 30px;
        font-size: 0.9rem;
        transition: color 0.3s;
    }
    
    .back-link:hover { color: #ff7820; }
    
    .stats {
        text-align: center;
        margin-bottom: 40px;
        font-size: 0.9rem;
        color: #8a7a6a;
    }
    
    .message-card {
        background: rgba(20, 15, 10, 0.9);
        border: 1px solid rgba(200, 170, 120, 0.2);
        border-left: 4px solid rgba(255, 120, 30, 0.3);
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 20px;
        transition: border-left-color 0.3s;
    }
    
    .message-card:hover {
        border-left-color: #ff7820;
    }
    
    .message-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 15px;
    }
    
    .message-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #ff7820, #ffa040);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    
    .message-author {
        font-weight: bold;
        color: #f4e4c1;
        font-size: 1.05rem;
    }
    
    .message-date {
        color: #6a5a4a;
        font-size: 0.8rem;
        margin-left: auto;
    }
    
    .message-text {
        color: #d4c5b0;
        line-height: 1.7;
        font-style: italic;
        margin-bottom: 15px;
    }
    
    .reply-badge {
        display: inline-block;
        background: rgba(255, 120, 30, 0.1);
        border: 1px solid rgba(255, 120, 30, 0.3);
        color: #ff7820;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: bold;
    }
    
    .message-link {
        display: inline-block;
        color: #c9a030;
        text-decoration: none;
        font-size: 0.85rem;
        margin-top: 10px;
        transition: color 0.3s;
    }
    
    .message-link:hover { color: #ff7820; }
    
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 15px;
        margin: 50px 0 30px;
    }
    
    .pagination a,
    .pagination span {
        padding: 10px 20px;
        border: 1px solid rgba(200, 170, 120, 0.2);
        border-radius: 8px;
        text-decoration: none;
        color: #d4c5b0;
        transition: all 0.3s;
    }
    
    .pagination a:hover {
        border-color: #ff7820;
        color: #ff7820;
    }
    
    .pagination .current {
        background: rgba(255, 120, 30, 0.1);
        border-color: #ff7820;
        color: #ff7820;
        font-weight: bold;
    }
    
    .pagination .disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }
    
    .cta-section {
        text-align: center;
        padding: 50px 20px;
        background: rgba(20, 15, 10, 0.5);
        border-radius: 15px;
        margin-top: 50px;
    }
    
    .cta-title {
        font-size: 1.6rem;
        color: #f4e4c1;
        margin-bottom: 15px;
    }
    
    .btn-primary {
        display: inline-block;
        background: linear-gradient(135deg, #ff7820, #ffa040);
        color: #fff;
        text-decoration: none;
        padding: 15px 35px;
        border-radius: 30px;
        font-weight: bold;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(255, 120, 30, 0.3);
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 25px rgba(255, 120, 30, 0.5);
    }
    
    .empty-state {
        text-align: center;
        padding: 80px 20px;
        color: #8a7a6a;
        font-style: italic;
    }
    
    @media (max-width: 600px) {
        body { padding: 20px 15px; }
        .header h1 { font-size: 1.8rem; }
        .message-card { padding: 20px 15px; }
        .message-header { flex-wrap: wrap; }
        .message-date { margin-left: 0; width: 100%; }
    }
    </style>
</head>
<body>
    <div class="container">
        <a href="/" class="back-link">← Voltar para o álbum</a>
        
        <header class="header">
            <h1>💬 Mensagens</h1>
            <p>O que os ouvintes estão dizendo sobre Memórias de um Pé Vermelho</p>
        </header>
        
        <div class="stats">
            📊 <?= $total ?> mensagem<?= $total != 1 ? 's' : '' ?> compartilhada<?= $total != 1 ? 's' : '' ?>
            <?php if ($totalPages > 1): ?>
                • Página <?= $page ?> de <?= $totalPages ?>
            <?php endif; ?>
        </div>
        
        <?php if (empty($currentMessages)): ?>
            <div class="empty-state">
                <p>Ainda não há mensagens públicas.</p>
            </div>
        <?php else: ?>
            <?php foreach ($currentMessages as $msg): ?>
            <article class="message-card">
                <div class="message-header">
                    <div class="message-avatar">🌾</div>
                    <span class="message-author"><?= htmlspecialchars($msg['name']) ?></span>
                    <?php if (!empty($msg['date'])): ?>
                        <span class="message-date">
                            <?= date('d/m/Y', strtotime($msg['date'])) ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="message-text">
                    "<?= nl2br(htmlspecialchars($msg['message'])) ?>"
                </div>
                
                <?php if (!empty($msg['reply'])): ?>
                    <span class="reply-badge">✅ Com resposta de Clebson Ribeiro</span>
                <?php endif; ?>
                
                <a href="/mensagem/<?= htmlspecialchars($msg['id']) ?>" class="message-link">
                    Ver mensagem completa →
                </a>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if ($totalPages > 1): ?>
        <nav class="pagination">
            <?php if ($page > 1): ?>
                <a href="/mensagens<?= $page > 2 ? '/' . ($page - 1) : '' ?>">← Anterior</a>
            <?php else: ?>
                <span class="disabled">← Anterior</span>
            <?php endif; ?>
            
            <span class="current"><?= $page ?></span>
            
            <?php if ($page < $totalPages): ?>
                <a href="/mensagens/<?= $page + 1 ?>">Próxima →</a>
            <?php else: ?>
                <span class="disabled">Próxima →</span>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
        
        <section class="cta-section">
            <h2 class="cta-title">Ouça o Álbum Completo</h2>
            <a href="/" class="btn-primary">🎧 Voltar ao Rádio</a>
        </section>
    </div>
</body>
</html>
