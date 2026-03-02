<?php
/**
 * Get Supporters Count
 * Retorna número de apoiadores
 * v1.0
 */

require_once __DIR__ . '/config-mp.php';

// Permite GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, ['error' => 'Método não permitido'], 405);
}

// ============================================
// CONTA APOIADORES CONFIRMADOS
// ============================================

$donations = loadJSON(DONATIONS_FILE, []);

// Conta apenas doações aprovadas
$approved = array_filter($donations, function($d) {
    return isset($d['status']) && $d['status'] === 'approved';
});

$total = count($approved);

// Salva em cache (otimização)
$cache = [
    'total' => $total,
    'last_updated' => date('c')
];
saveJSON(SUPPORTERS_COUNT_FILE, $cache);

// Retorna
jsonResponse(true, [
    'total' => $total,
    'next_position' => $total + 1
]);
