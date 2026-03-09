<?php
// ============================================================
// ADMIN — Memórias de um Pé Vermelho
// Acesso: /admin/?token=SEU_TOKEN
// ============================================================

define('ADMIN_TOKEN',    '82941092900');
define('MESSAGES_FILE',  __DIR__ . '/../api/messages.json');
define('PLAYS_FILE',     __DIR__ . '/../api/play-counts.json');

// --- Autenticação ---
$token = $_GET['token'] ?? '';
if ($token !== ADMIN_TOKEN) {
    http_response_code(403);
    die('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Acesso Negado</title>
    <style>body{background:#0a0a0a;color:#f4e4c1;font-family:Georgia,serif;display:flex;
    align-items:center;justify-content:center;height:100vh;margin:0;font-size:1.5rem;}
    </style></head><body>🔒 Acesso negado.</body></html>');
}

// --- Exportar CSV ---
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $messages = json_decode(file_get_contents(MESSAGES_FILE), true) ?? [];
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="contatos-memorias-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
    fputcsv($out, ['Nome', 'E-mail', 'WhatsApp', 'Consentimento', 'Data', 'Mensagem']);
    foreach (array_reverse($messages) as $m) {
        if (!empty($m['_email']) || !empty($m['_whatsapp'])) {
            fputcsv($out, [
                $m['name']      ?? '',
                $m['_email']    ?? '',
                $m['_whatsapp'] ?? '',
                !empty($m['_consent']) ? 'Sim' : 'Não',
                isset($m['date']) ? date('d/m/Y H:i', strtotime($m['date'])) : '',
                $m['message']   ?? '',
            ]);
        }
    }
    fclose($out);
    exit;
}

// --- Carrega dados ---
$messages   = json_decode(@file_get_contents(MESSAGES_FILE), true) ?? [];
$playCounts = json_decode(@file_get_contents(PLAYS_FILE),    true) ?? [];

$tracks = [
    '0' => 'Cabocla / Chico Mineiro / Nhambu Xita',
    '1' => 'Romaria',
    '2' => 'Tocando em Frente',
    '3' => 'Amargurado',
    '4' => 'Antonio e Natália',
    '5' => 'Vaca Estrela e Boi Fubá',
    '6' => 'Disparada',
    '7' => 'Mercedita',
    '8' => 'Vide Vida Marvada',
    '9' => 'A Terra e o Tempo',
];

// --- Estatísticas ---
$totalPlays    = array_sum($playCounts);
$totalMessages = count($messages);
$withEmail     = count(array_filter($messages, fn($m) => !empty($m['_email'])));
$withWhatsapp  = count(array_filter($messages, fn($m) => !empty($m['_whatsapp'])));
$withConsent   = count(array_filter($messages, fn($m) => !empty($m['_consent'])));
$withContact   = count(array_filter($messages, fn($m) => !empty($m['_email']) || !empty($m['_whatsapp'])));

// Ranking músicas
$ranking = [];
foreach ($tracks as $id => $name) {
    $ranking[$id] = ['name' => $name, 'plays' => (int)($playCounts[$id] ?? 0)];
}
uasort($ranking, fn($a, $b) => $b['plays'] - $a['plays']);
$maxPlays = max(array_column($ranking, 'plays')) ?: 1;

// Últimas 20 mensagens
$lastMessages = array_slice(array_reverse($messages), 0, 20);

// Helper para escapar JSON com segurança
function safeJsonEncode($value) {
    return json_encode($value ?? '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — Memórias de um Pé Vermelho</title>
<style>
:root {
    --bg:       #0d0d0d;
    --surface:  #161410;
    --border:   rgba(200,160,40,0.15);
    --gold:     #c9a030;
    --orange:   #ff7820;
    --text:     #d4c5b0;
    --muted:    #8a7a6a;
    --dim:      #6a5a4a;
    --success:  #4a9060;
    --danger:   #904040;
}

* { margin:0; padding:0; box-sizing:border-box; }

body {
    background: var(--bg);
    color: var(--text);
    font-family: Georgia, 'Times New Roman', serif;
    min-height: 100vh;
    padding: 0 0 60px;
}

/* ---- Header ---- */
.adm-header {
    background: linear-gradient(135deg, #161410 0%, #0d0d0d 100%);
    border-bottom: 1px solid var(--border);
    padding: 28px 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
    position: sticky;
    top: 0;
    z-index: 100;
}

.adm-logo { font-size: 1.4rem; color: var(--gold); letter-spacing: 1px; }
.adm-logo span { color: var(--muted); font-size: 0.85rem; display: block; margin-top: 2px; }

.adm-time { font-size: 0.8rem; color: var(--dim); font-style: italic; }

/* ---- Layout ---- */
.adm-body { max-width: 1100px; margin: 0 auto; padding: 40px 20px 0; }

/* ---- KPI Cards ---- */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: 16px;
    margin-bottom: 40px;
}

.kpi-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 22px 20px;
    text-align: center;
    transition: border-color 0.3s;
}

.kpi-card:hover { border-color: rgba(200,160,40,0.35); }

.kpi-value {
    font-size: 2.4rem;
    font-weight: bold;
    color: var(--orange);
    line-height: 1;
    margin-bottom: 8px;
}

.kpi-label { font-size: 0.8rem; color: var(--muted); letter-spacing: 1px; text-transform: uppercase; }

/* ---- Seções ---- */
.adm-section {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 30px;
    margin-bottom: 30px;
}

.section-title {
    font-size: 1.1rem;
    color: #f4e4c1;
    letter-spacing: 2px;
    margin-bottom: 25px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 10px;
}

/* ---- Ranking ---- */
.track-row {
    display: grid;
    grid-template-columns: 22px 1fr 80px 160px;
    align-items: center;
    gap: 15px;
    padding: 10px 0;
    border-bottom: 1px solid rgba(200,160,40,0.06);
}

.track-row:last-child { border-bottom: none; }

.track-pos { color: var(--orange); font-weight: bold; font-size: 0.9rem; text-align: right; }

.track-name-cell { font-size: 0.92rem; color: var(--text); }

.track-plays-num {
    text-align: right;
    font-size: 0.9rem;
    color: var(--gold);
    font-weight: bold;
}

.track-bar-wrap { height: 8px; background: rgba(255,255,255,0.05); border-radius: 4px; overflow: hidden; }

.track-bar-fill {
    height: 100%;
    border-radius: 4px;
    background: linear-gradient(90deg, var(--orange), #ffa040);
    transition: width 1s ease;
}

/* ---- Mensagens ---- */
.msg-card {
    padding: 16px 18px;
    background: rgba(10,8,5,0.5);
    border-left: 3px solid rgba(255,120,30,0.3);
    border-radius: 8px;
    margin-bottom: 12px;
    transition: border-left-color 0.2s;
}

.msg-card:hover { border-left-color: var(--orange); }

.msg-text { font-size: 0.95rem; color: var(--text); line-height: 1.6; font-style: italic; margin-bottom: 10px; }

.msg-meta { display: flex; gap: 15px; flex-wrap: wrap; font-size: 0.78rem; color: var(--dim); }

.msg-meta strong { color: var(--muted); }

.badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 0.72rem;
    font-weight: bold;
}

.badge-green { background: rgba(74,144,96,0.2); color: #7acc9a; border: 1px solid rgba(74,144,96,0.3); }
.badge-dim   { background: rgba(100,80,60,0.2); color: var(--dim); border: 1px solid rgba(100,80,60,0.2); }

/* ---- Tabela de Contatos ---- */
.contacts-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 10px;
}

.export-btn {
    background: linear-gradient(135deg, var(--orange), #ffa040);
    color: #fff;
    border: none;
    padding: 10px 22px;
    border-radius: 20px;
    font-size: 0.88rem;
    font-weight: bold;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;

    transition: all 0.3s;
    font-family: Georgia, serif;
}

.export-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(255,120,30,0.4); }

.contacts-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }

.contacts-table th {
    text-align: left;
    padding: 10px 12px;
    color: var(--muted);
    font-size: 0.75rem;
    letter-spacing: 1px;
    text-transform: uppercase;
    border-bottom: 1px solid var(--border);
    font-weight: normal;
}

.contacts-table td {
    padding: 12px 12px;
    border-bottom: 1px solid rgba(200,160,40,0.06);
    color: var(--text);
    vertical-align: middle;
}

.contacts-table tr:hover td { background: rgba(255,120,30,0.03); }

.contacts-table tr:last-child td { border-bottom: none; }

.no-data { color: var(--dim); font-style: italic; font-size: 0.88rem; }

/* ---- Paginação mensagens ---- */
.show-more {
    background: none;
    border: 1px solid var(--border);
    color: var(--muted);
    padding: 10px 25px;
    border-radius: 20px;
    cursor: pointer;
    font-size: 0.85rem;
    font-family: Georgia, serif;
    display: block;
    margin: 15px auto 0;
    transition: all 0.3s;
}

.show-more:hover { border-color: var(--orange); color: var(--orange); }

/* ---- Empty state ---- */
.empty { text-align: center; padding: 40px 20px; color: var(--dim); font-style: italic; }

/* ---- Responsive ---- */
@media (max-width: 700px) {
    .adm-header { padding: 20px; }
    .adm-body   { padding: 20px 15px 0; }
    .adm-section { padding: 20px 15px; }
    .track-row  { grid-template-columns: 20px 1fr 50px; }
    .track-bar-wrap { display: none; }
    .contacts-table th:nth-child(4),
    .contacts-table td:nth-child(4) { display: none; }
}

/* ============================================================
   MODAL DE RESPOSTA
   ============================================================ */

.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(5px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.2s;
}

.modal-overlay.active { display: flex; }

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-box {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 35px;
    max-width: 600px;
    width: 90%;
    max-height: 85vh;
    overflow-y: auto;
    box-shadow: 0 20px 80px rgba(0, 0, 0, 0.9);
    animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--border);
}

.modal-title {
    font-size: 1.2rem;
    color: var(--gold);
}

.modal-close {
    background: none;
    border: none;
    color: var(--muted);
    font-size: 1.5rem;
    cursor: pointer;
    padding: 5px 10px;
    transition: color 0.2s;
}

.modal-close:hover { color: var(--text); }

.modal-message {
    background: rgba(10, 8, 5, 0.5);
    border-left: 3px solid rgba(255, 120, 30, 0.3);
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 25px;
}

.modal-message-label {
    font-size: 0.75rem;
    color: var(--dim);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 8px;
}

.modal-message-text {
    color: var(--text);
    font-style: italic;
    line-height: 1.6;
}

.modal-form-group {
    margin-bottom: 20px;
}

.modal-form-group label {
    display: block;
    font-size: 0.85rem;
    color: var(--muted);
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.modal-textarea {
    width: 100%;
    background: rgba(10, 10, 10, 0.6);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 12px;
    color: var(--text);
    font-family: Georgia, serif;
    font-size: 0.95rem;
    min-height: 120px;
    resize: vertical;
    transition: border-color 0.3s;
}

.modal-textarea:focus {
    outline: none;
    border-color: var(--orange);
}

.modal-char-count {
    font-size: 0.75rem;
    color: var(--dim);
    text-align: right;
    margin-top: 5px;
}

.modal-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 25px;
}

.modal-btn {
    padding: 10px 25px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-family: Georgia, serif;
    cursor: pointer;
    transition: all 0.3s;
    border: none;
}

.modal-btn-cancel {
    background: rgba(100, 80, 60, 0.2);
    color: var(--muted);
    border: 1px solid var(--border);
}

.modal-btn-cancel:hover {
    background: rgba(100, 80, 60, 0.3);
    color: var(--text);
}

.modal-btn-submit {
    background: linear-gradient(135deg, var(--orange), #ffa040);
    color: #fff;
    font-weight: bold;
}

.modal-btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(255, 120, 30, 0.4);
}

.modal-btn-submit:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.msg-actions {
    margin-top: 12px;
    display: flex;
    gap: 10px;
}

.reply-btn {
    background: rgba(255, 120, 30, 0.1);
    border: 1px solid rgba(255, 120, 30, 0.3);
    color: var(--orange);
    padding: 6px 15px;
    border-radius: 15px;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.3s;
    font-family: Georgia, serif;
}

.reply-btn:hover {
    background: rgba(255, 120, 30, 0.2);
    border-color: var(--orange);
}

.msg-reply {
    margin-top: 15px;
    padding: 12px 15px;
    background: rgba(255, 120, 30, 0.05);
    border-left: 3px solid var(--orange);
    border-radius: 5px;
}

.msg-reply-label {
    font-size: 0.75rem;
    color: var(--orange);
    font-weight: bold;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.msg-reply-text {
    color: var(--text);
    line-height: 1.6;
    font-size: 0.9rem;
}

.msg-reply-date {
    font-size: 0.7rem;
    color: var(--dim);
    margin-top: 8px;
}

</style>
</head>
<body>

<!-- Header -->
<header class="adm-header">
    <div class="adm-logo">
        📻 Memórias de um Pé Vermelho
        <span>Painel Administrativo</span>
    </div>
    <div class="adm-time">
        Atualizado em <?= date('d/m/Y \à\s H:i') ?>
    </div>
</header>

<div class="adm-body">

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-value"><?= number_format($totalPlays, 0, ',', '.') ?></div>
            <div class="kpi-label">Total de Audições</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value"><?= $totalMessages ?></div>
            <div class="kpi-label">Mensagens</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value"><?= $withContact ?></div>
            <div class="kpi-label">Com Contato</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value"><?= $withEmail ?></div>
            <div class="kpi-label">E-mails</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value"><?= $withWhatsapp ?></div>
            <div class="kpi-label">WhatsApps</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value"><?= $withConsent ?></div>
            <div class="kpi-label">Consentimentos</div>
        </div>
    </div>

    <!-- Ranking de Músicas -->
    <section class="adm-section">
        <h2 class="section-title">📊 Ranking de Audições</h2>

        <?php if ($totalPlays === 0): ?>
            <p class="empty">Nenhuma audição registrada ainda.</p>
        <?php else: ?>
            <?php $pos = 1; foreach ($ranking as $id => $track): ?>
            <div class="track-row">
                <div class="track-pos"><?= $pos++ ?></div>
                <div class="track-name-cell"><?= htmlspecialchars($track['name']) ?></div>
                <div class="track-plays-num"><?= $track['plays'] ?> ▶</div>
                <div class="track-bar-wrap">
                    <div class="track-bar-fill" style="width:<?= round($track['plays'] / $maxPlays * 100) ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <!-- Últimas Mensagens -->
    <section class="adm-section">
        <h2 class="section-title">💬 Últimas Mensagens</h2>

        <?php if (empty($messages)): ?>
            <p class="empty">Nenhuma mensagem ainda.</p>
        <?php else: ?>
            <?php foreach ($lastMessages as $i => $m): ?>
            <div class="msg-card" <?= $i >= 5 ? 'class="msg-card extra" style="display:none"' : '' ?>>
                <p class="msg-text">"<?= htmlspecialchars($m['message'] ?? '') ?>"</p>
                <div class="msg-meta">
                    <strong>🌾 <?= htmlspecialchars($m['name'] ?? '—') ?></strong>
                    <?php if (!empty($m['_email'])): ?>
                        <span>✉️ <?= htmlspecialchars($m['_email']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($m['_whatsapp'])): ?>
                        <span>📱 <?= htmlspecialchars($m['_whatsapp']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($m['date'])): ?>
                        <span>🕐 <?= date('d/m/Y H:i', strtotime($m['date'])) ?></span>
                    <?php endif; ?>
                    <span class="badge <?= !empty($m['_consent']) ? 'badge-green' : 'badge-dim' ?>">
                        <?= !empty($m['_consent']) ? '✓ Consentiu' : 'Sem consentimento' ?>
                    </span>
                </div>
                
                <!-- Resposta se houver -->
                <?php if (!empty($m['reply'])): ?>
                <div class="msg-reply">
                    <div class="msg-reply-label">
                        🎵 Resposta de Clebson Ribeiro
                    </div>
                    <div class="msg-reply-text"><?= nl2br(htmlspecialchars($m['reply']['text'])) ?></div>
                    <div class="msg-reply-date">
                        <?= date('d/m/Y H:i', strtotime($m['reply']['date'])) ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Botão Responder -->
                <div class="msg-actions">
                    <button class="reply-btn" onclick='openReplyModal(<?= json_encode($m["id"]) ?>,<?= json_encode($m["name"] ?? "") ?>,<?= json_encode($m["message"] ?? "") ?>,<?= !empty($m["_email"]) ? "true" : "false" ?>)'>
                        <?= empty($m['reply']) ? '💬 Responder' : '✏️ Editar Resposta' ?>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (count($lastMessages) > 5): ?>
            <button class="show-more" onclick="
                document.querySelectorAll('.msg-card').forEach(el => el.style.display = 'block');
                this.style.display = 'none';
            ">Ver todas (<?= count($lastMessages) ?>)</button>
            <?php endif; ?>
        <?php endif; ?>
    </section>

    <!-- Lista de Contatos -->
    <section class="adm-section">
        <div class="contacts-toolbar">
            <h2 class="section-title" style="margin:0;border:none;padding:0;">
                📋 Contatos (<?= $withContact ?>)
            </h2>
            <?php if ($withContact > 0): ?>
            <a href="?token=<?= htmlspecialchars($token) ?>&export=csv" class="export-btn">
                📥 Exportar CSV
            </a>
            <?php endif; ?>
        </div>

        <?php
        $contacts = array_filter($messages, fn($m) => !empty($m['_email']) || !empty($m['_whatsapp']));
        $contacts = array_reverse($contacts);
        ?>

        <?php if (empty($contacts)): ?>
            <p class="empty">Nenhum contato registrado ainda.</p>
        <?php else: ?>
        <table class="contacts-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>WhatsApp</th>
                    <th>Consentimento</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['name'] ?? '—') ?></td>
                    <td><?= !empty($m['_email']) ? htmlspecialchars($m['_email']) : '<span class="no-data">—</span>' ?></td>
                    <td><?= !empty($m['_whatsapp']) ? htmlspecialchars($m['_whatsapp']) : '<span class="no-data">—</span>' ?></td>
                    <td>
                        <span class="badge <?= !empty($m['_consent']) ? 'badge-green' : 'badge-dim' ?>">
                            <?= !empty($m['_consent']) ? '✓ Sim' : 'Não' ?>
                        </span>
                    </td>
                    <td><?= !empty($m['date']) ? date('d/m/Y H:i', strtotime($m['date'])) : '—' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </section>

</div><!-- /adm-body -->

<!-- Modal de Resposta -->
<div class="modal-overlay" id="replyModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title">💬 Responder Mensagem</h3>
            <button class="modal-close" onclick="closeReplyModal()">&times;</button>
        </div>

        <div class="modal-message">
            <div class="modal-message-label">Mensagem de <span id="modalUserName"></span></div>
            <div class="modal-message-text" id="modalUserMessage"></div>
        </div>

        <form id="replyForm" onsubmit="submitReply(event)">
            <input type="hidden" id="replyMessageId">
            
            <div class="modal-form-group">
                <label for="replyText">Sua Resposta <span style="color: var(--orange);">*</span></label>
                <textarea 
                    id="replyText" 
                    class="modal-textarea" 
                    placeholder="Digite sua resposta..."
                    maxlength="500"
                    required
                ></textarea>
                <div class="modal-char-count">
                    <span id="replyCharCount">0</span> / 500 caracteres
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="modal-btn modal-btn-cancel" onclick="closeReplyModal()">
                    Cancelar
                </button>
                <button type="submit" class="modal-btn modal-btn-submit" id="replySubmitBtn">
                    Enviar Resposta
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Contador de caracteres
document.getElementById('replyText').addEventListener('input', function() {
    document.getElementById('replyCharCount').textContent = this.value.length;
});

// Abrir modal
function openReplyModal(messageId, userName, userMessage, hasEmail) {
    document.getElementById('replyMessageId').value = messageId;
    document.getElementById('modalUserName').textContent = userName;
    document.getElementById('modalUserMessage').textContent = userMessage;
    document.getElementById('replyText').value = '';
    document.getElementById('replyCharCount').textContent = '0';
    document.getElementById('replyModal').classList.add('active');
    document.getElementById('replyText').focus();
}

// Fechar modal
function closeReplyModal() {
    document.getElementById('replyModal').classList.remove('active');
}

// Fechar com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeReplyModal();
});

// Enviar resposta
async function submitReply(event) {
    event.preventDefault();
    
    const btn = document.getElementById('replySubmitBtn');
    const messageId = document.getElementById('replyMessageId').value;
    const replyText = document.getElementById('replyText').value.trim();
    
    if (!replyText) {
        alert('Por favor, escreva uma resposta.');
        return;
    }
    
    btn.disabled = true;
    btn.textContent = 'Enviando...';
    
    try {
        const response = await fetch('../api/messages.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Admin-Token': '<?= ADMIN_TOKEN ?>'
            },
            body: JSON.stringify({
                message_id: messageId,
                reply: replyText
            })
        });
        
        const text = await response.text();
        
        if (!text) {
            alert('❌ Erro: API retornou vazio');
            btn.disabled = false;
            btn.textContent = 'Enviar Resposta';
            return;
        }
        
        const result = JSON.parse(text);
        
        if (result.success) {
            alert('✅ Resposta enviada! Email enviado automaticamente (se disponível).');
            closeReplyModal();
            location.reload(); // Recarrega para mostrar resposta
        } else {
            alert('❌ Erro: ' + (result.message || 'Tente novamente'));
        }
    } catch (error) {
        alert('❌ Erro de conexão. Tente novamente.');
        console.error(error);
    } finally {
        btn.disabled = false;
        btn.textContent = 'Enviar Resposta';
    }
}
</script>

</body>
</html>