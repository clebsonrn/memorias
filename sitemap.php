<?php
/**
 * Sitemap.xml Dinâmico
 * URL: /sitemap.xml
 */

header('Content-Type: application/xml; charset=utf-8');

define('MESSAGES_FILE', __DIR__ . '/api/messages.json');
define('SITE_URL', 'https://memorias.clebsonribeiro.com.br');

// Carrega mensagens
$messages = json_decode(@file_get_contents(MESSAGES_FILE), true) ?? [];

// Inicia XML
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    
    <!-- Página Principal -->
    <url>
        <loc><?= SITE_URL ?>/</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>
    
    <!-- Feed de Mensagens -->
    <url>
        <loc><?= SITE_URL ?>/mensagens</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    
    <!-- Mensagens Individuais -->
    <?php foreach ($messages as $msg): ?>
    <?php if (!empty($msg['id'])): ?>
    <url>
        <loc><?= SITE_URL ?>/mensagem/<?= htmlspecialchars($msg['id']) ?></loc>
        <lastmod><?= !empty($msg['date']) ? date('Y-m-d', strtotime($msg['date'])) : date('Y-m-d') ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    <?php endif; ?>
    <?php endforeach; ?>
    
</urlset>
