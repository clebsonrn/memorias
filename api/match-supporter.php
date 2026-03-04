<?php
/**
 * match-supporter.php v2
 * Arquivo: /var/www/memorias/api/match-supporter.php
 *
 * GET  ?donation_id=don_xxx          → matching automático (debug)
 * POST { donation_id }               → matching automático
 * POST { donation_id, message_id }   → matching manual
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://memorias.clebsonribeiro.com.br');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// config-mp.php está na mesma pasta /api/
require_once __DIR__ . '/config-mp.php';

// messages.json também está em /api/ (mesmo que este arquivo)
// DATA_FILE = __DIR__ . '/messages.json' definido em api/messages.php
// DONATIONS_FILE = __DIR__ . '/donations.json' já definido no config-mp.php
define('MESSAGES_FILE', __DIR__ . '/messages.json');

// ── Entry point ───────────────────────────────────────────────────────────────

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $donationId = $_GET['donation_id'] ?? null;
    if (!$donationId) respondMatch(['error' => 'donation_id obrigatório'], 400);
    respondMatch(matchSupporterByDonation($donationId));
}

if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);

    if (!empty($body['message_id']) && !empty($body['donation_id'])) {
        respondMatch(applyManualMatch($body['donation_id'], $body['message_id']));
    }

    if (!empty($body['donation_id'])) {
        respondMatch(matchSupporterByDonation($body['donation_id']));
    }

    respondMatch(['error' => 'Parâmetros inválidos'], 400);
}

// ── Matching principal ────────────────────────────────────────────────────────

function matchSupporterByDonation(string $donationId): array {

    $donations = loadJSON(DONATIONS_FILE, []);
    $donation  = null;
    foreach ($donations as $d) {
        if (($d['id'] ?? '') === $donationId) { $donation = $d; break; }
    }

    if (!$donation)                         return ['success' => false, 'error' => 'Doação não encontrada'];
    if ($donation['status'] !== 'approved') return ['success' => false, 'error' => 'Doação não aprovada'];

    $messages          = loadJSON(MESSAGES_FILE, []);
    $supporterPosition = getSupporterPosition($donationId, $donations);

    // Nível 1: Email
    if (!empty($donation['payer_email'])) {
        $matched = matchByEmail($donation['payer_email'], $messages);
        if ($matched) {
            applyMatch($matched['id'], $donationId, $supporterPosition, 'email', $donation);
            logDebug('MATCH EMAIL', ['donation' => $donationId, 'message' => $matched['id']]);
            return [
                'success'            => true,
                'method'             => 'email',
                'message_id'         => $matched['id'],
                'supporter_position' => $supporterPosition,
            ];
        }
    }

    // Nível 2: Fingerprint
    if (!empty($donation['fingerprint'])) {
        $matched = matchByFingerprint($donation['fingerprint'], $messages);
        if ($matched) {
            applyMatch($matched['id'], $donationId, $supporterPosition, 'fingerprint', $donation);
            logDebug('MATCH FINGERPRINT', ['donation' => $donationId, 'message' => $matched['id']]);
            return [
                'success'            => true,
                'method'             => 'fingerprint',
                'message_id'         => $matched['id'],
                'supporter_position' => $supporterPosition,
            ];
        }
    }

    // Nível 3: seleção manual
    logDebug('SEM MATCH AUTO', ['donation' => $donationId]);
    return [
        'success'            => false,
        'method'             => 'pending_manual',
        'donation_id'        => $donationId,
        'supporter_position' => $supporterPosition,
        'candidates'         => getRecentCandidates($messages),
        'message'            => 'Selecione sua mensagem no feed.',
    ];
}

function applyManualMatch(string $donationId, string $messageId): array {

    $donations = loadJSON(DONATIONS_FILE, []);
    $donation  = null;
    foreach ($donations as $d) {
        if (($d['id'] ?? '') === $donationId) { $donation = $d; break; }
    }

    if (!$donation || $donation['status'] !== 'approved') {
        return ['success' => false, 'error' => 'Doação inválida ou não aprovada'];
    }

    $messages = loadJSON(MESSAGES_FILE, []);
    $exists   = false;
    foreach ($messages as $m) {
        if (($m['id'] ?? '') === $messageId) { $exists = true; break; }
    }
    if (!$exists) return ['success' => false, 'error' => 'Mensagem não encontrada'];

    $position = getSupporterPosition($donationId, $donations);
    applyMatch($messageId, $donationId, $position, 'manual', $donation);
    logDebug('MATCH MANUAL', ['donation' => $donationId, 'message' => $messageId]);

    return [
        'success'            => true,
        'method'             => 'manual',
        'message_id'         => $messageId,
        'supporter_position' => $position,
    ];
}

// ── Helpers de matching ───────────────────────────────────────────────────────

function matchByEmail(string $email, array $messages): ?array {
    $email = strtolower(trim($email));
    foreach ($messages as $msg) {
        if (!empty($msg['_email']) && strtolower(trim($msg['_email'])) === $email) {
            return $msg;
        }
    }
    return null;
}

function matchByFingerprint(string $fp, array $messages): ?array {
    foreach ($messages as $msg) {
        if (!empty($msg['_fingerprint']) && $msg['_fingerprint'] === $fp) {
            return $msg;
        }
    }
    return null;
}

function getRecentCandidates(array $messages, int $limit = 10): array {
    $candidates = array_filter($messages, fn($m) => empty($m['is_supporter']));
    usort($candidates, fn($a, $b) => strtotime($b['date'] ?? '0') - strtotime($a['date'] ?? '0'));
    return array_map(fn($m) => [
        'id'         => $m['id'],
        'name'       => $m['name'] ?? 'Anônimo',
        'preview'    => mb_substr($m['message'] ?? '', 0, 80),
        'created_at' => $m['date'] ?? '',
    ], array_values(array_slice($candidates, 0, $limit)));
}

// ── Persistência ─────────────────────────────────────────────────────────────

function applyMatch(string $messageId, string $donationId, int $position, string $method, array $donation): void {

    // Atualiza api/messages.json
    $messages = loadJSON(MESSAGES_FILE, []);
    foreach ($messages as &$msg) {
        if (($msg['id'] ?? '') === $messageId) {
            $msg['is_supporter']           = true;
            $msg['supporter_position']     = $position;
            $msg['supporter_donation_id']  = $donationId;
            $msg['supporter_match_method'] = $method;
            $msg['supporter_since']        = $donation['approved_at'] ?? date('c');
            break;
        }
    }
    unset($msg);
    saveJSON(MESSAGES_FILE, $messages);

    // Atualiza api/donations.json
    $donations = loadJSON(DONATIONS_FILE, []);
    foreach ($donations as &$d) {
        if (($d['id'] ?? '') === $donationId) {
            $d['matched_message_id'] = $messageId;
            $d['match_method']       = $method;
            $d['matched_at']         = date('c');
            break;
        }
    }
    unset($d);
    saveJSON(DONATIONS_FILE, $donations);
}

function getSupporterPosition(string $donationId, array $donations): int {
    $approved = array_filter($donations, fn($d) => ($d['status'] ?? '') === 'approved');
    usort($approved, fn($a, $b) => strtotime($a['approved_at'] ?? '0') - strtotime($b['approved_at'] ?? '0'));
    $pos = 1;
    foreach ($approved as $d) {
        if ($d['id'] === $donationId) return $pos;
        $pos++;
    }
    return $pos;
}

// ── Utilitário ────────────────────────────────────────────────────────────────

function respondMatch(array $data, int $status = 200): never {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}