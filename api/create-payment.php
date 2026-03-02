<?php
/**
 * Create Payment - Mercado Pago
 * Cria preferência de pagamento
 * v1.0
 */

require_once __DIR__ . '/config-mp.php';

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, ['error' => 'Método não permitido'], 405);
}

// Pega dados
$input = json_decode(file_get_contents('php://input'), true);

$amount = $input['amount'] ?? null;
$email = $input['email'] ?? null;
$fingerprint = $input['fingerprint'] ?? null;

// Valida valor
if (!$amount) {
    jsonResponse(false, ['error' => 'Valor não informado'], 400);
}

$validation = validateAmount($amount);
if (!$validation['valid']) {
    jsonResponse(false, ['error' => $validation['error']], 400);
}

$amount = $validation['amount'];

// Log
logDebug('Nova solicitação de pagamento', [
    'amount' => $amount,
    'email' => $email,
    'fingerprint' => substr($fingerprint, 0, 20) . '...'
]);

// ============================================
// CRIA PREFERÊNCIA NO MERCADO PAGO
// ============================================

try {
    $preference = [
        'items' => [
            [
                'title' => 'Apoio ao álbum "' . SITE_NAME . '"',
                'description' => 'Doação para ' . ARTIST_NAME,
                'quantity' => 1,
                'unit_price' => $amount,
                'currency_id' => 'BRL'
            ]
        ],
        'payer' => [
            'email' => $email ?: 'apoio@memorias.com.br'
        ],
        'back_urls' => [
            'success' => SITE_URL . '?payment=success',
            'failure' => SITE_URL . '?payment=failure',
            'pending' => SITE_URL . '?payment=pending'
        ],
        'auto_return' => 'approved',
        'notification_url' => WEBHOOK_URL,
        'statement_descriptor' => 'MEMORIAS PE VERMELHO',
        'external_reference' => generateId('don'),
        'metadata' => [
            'fingerprint' => $fingerprint,
            'email' => $email,
            'timestamp' => time()
        ]
    ];
    
    // Chama API do Mercado Pago
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.mercadopago.com/checkout/preferences');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($preference));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . MP_ACCESS_TOKEN,
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 201) {
        logDebug('Erro ao criar preferência MP', [
            'http_code' => $httpCode,
            'response' => $response
        ]);
        
        jsonResponse(false, ['error' => 'Erro ao criar pagamento. Tente novamente.'], 500);
    }
    
    $result = json_decode($response, true);
    
    // Salva referência local
    $donation = [
        'id' => $result['external_reference'],
        'preference_id' => $result['id'],
        'amount' => $amount,
        'email' => $email,
        'fingerprint' => $fingerprint,
        'status' => 'pending',
        'created_at' => date('c'),
        'init_point' => $result['init_point']
    ];
    
    // Salva no arquivo
    $donations = loadJSON(DONATIONS_FILE, []);
    $donations[] = $donation;
    saveJSON(DONATIONS_FILE, $donations);
    
    logDebug('Preferência criada com sucesso', [
        'preference_id' => $result['id'],
        'external_ref' => $result['external_reference']
    ]);
    
    // Retorna dados para o frontend
    jsonResponse(true, [
        'preference_id' => $result['id'],
        'init_point' => $result['init_point'],
        'sandbox_init_point' => $result['sandbox_init_point'] ?? null,
        'public_key' => MP_PUBLIC_KEY
    ]);
    
} catch (Exception $e) {
    logDebug('Exceção ao criar pagamento', [
        'error' => $e->getMessage()
    ]);
    
    jsonResponse(false, ['error' => 'Erro interno. Tente novamente.'], 500);
}
