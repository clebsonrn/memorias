<?php
/**
 * Configuração Mercado Pago
 * Memórias de um Pé Vermelho
 * v1.0
 */

// ============================================
// CREDENCIAIS MERCADO PAGO (TESTE)
// ============================================

// IMPORTANTE: Trocar para produção antes do lançamento!
define('MP_PUBLIC_KEY', 'APP_USR-67d9421f-a8f2-4a0e-9ec0-e12b061e8ea5');
define('MP_ACCESS_TOKEN', 'APP_USR-6422798141765798-030309-c5313b160e16b5492124248657df9a47-49625713');

// ============================================
// CONFIGURAÇÕES DO PROJETO
// ============================================

define('SITE_URL', 'https://memorias.clebsonribeiro.com.br');
define('SITE_NAME', 'Memórias de um Pé Vermelho');
define('ARTIST_NAME', 'Clebson Ribeiro');
define('ARTIST_EMAIL', 'contato@clebsonribeiro.com.br');

// ============================================
// ARQUIVOS DE DADOS
// ============================================

define('DONATIONS_FILE', __DIR__ . '/donations.json');
define('SUPPORTERS_COUNT_FILE', __DIR__ . '/supporters-count.json');

// ============================================
// LIMITES
// ============================================

define('MIN_DONATION', 1.00);      // R$ 1,00 mínimo
define('MAX_DONATION', 1000.00);   // R$ 1.000,00 máximo

// ============================================
// WEBHOOK
// ============================================

define('WEBHOOK_URL', SITE_URL . '/api/webhook-mp.php');

// ============================================
// FUNÇÕES AUXILIARES
// ============================================

/**
 * Carrega JSON com segurança
 */
function loadJSON($file, $default = []) {
    if (!file_exists($file)) {
        return $default;
    }
    
    $content = file_get_contents($file);
    $data = json_decode($content, true);
    
    return $data ?? $default;
}

/**
 * Salva JSON com segurança
 */
function saveJSON($file, $data) {
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($file, $json, LOCK_EX);
}

/**
 * Resposta JSON padronizada
 */
function jsonResponse($success, $data = [], $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: ' . SITE_URL);
    header('Access-Control-Allow-Methods: GET, POST');
    header('Access-Control-Allow-Headers: Content-Type');
    
    echo json_encode(array_merge(['success' => $success], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Gera ID único
 */
function generateId($prefix = 'don') {
    return $prefix . '_' . uniqid() . '_' . bin2hex(random_bytes(4));
}

/**
 * Valida valor de doação
 */
function validateAmount($amount) {
    $amount = floatval($amount);
    
    if ($amount < MIN_DONATION) {
        return ['valid' => false, 'error' => 'Valor mínimo: R$ ' . number_format(MIN_DONATION, 2, ',', '.')];
    }
    
    if ($amount > MAX_DONATION) {
        return ['valid' => false, 'error' => 'Valor máximo: R$ ' . number_format(MAX_DONATION, 2, ',', '.')];
    }
    
    return ['valid' => true, 'amount' => $amount];
}

/**
 * Log de debug
 */
function logDebug($message, $data = null) {
    $logFile = __DIR__ . '/debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}";
    
    if ($data !== null) {
        $logMessage .= "\n" . print_r($data, true);
    }
    
    $logMessage .= "\n---\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}
