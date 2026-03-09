<?php
/**
 * Webhook Mercado Pago
 * Recebe notificações de pagamento
 * v2.0 - Suporte formato novo e antigo
 */

require_once __DIR__ . '/config-mp.php';

// =====================================================
// LOG DA REQUISIÇÃO
// =====================================================

logDebug('Webhook recebido', [
    'method' => $_SERVER['REQUEST_METHOD'],
    'headers' => getallheaders(),
    'get' => $_GET,
    'post' => $_POST,
    'body' => file_get_contents('php://input')
]);

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logDebug('Método não permitido', ['method' => $_SERVER['REQUEST_METHOD']]);
    http_response_code(405);
    exit;
}

// =====================================================
// CAPTURA PAYLOAD
// =====================================================

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    logDebug('JSON inválido', ['input' => $input]);
    http_response_code(400);
    exit;
}

// =====================================================
// SUPORTE FORMATO NOVO E ANTIGO
// =====================================================

$type = null;
$id = null;

// Formato NOVO: {"type":"payment","data":{"id":"123"}}
if (isset($data['type']) && isset($data['data']['id'])) {
    $type = $data['type'];
    $id = $data['data']['id'];
}
// Formato ANTIGO: {"topic":"payment","resource":".../123"}
elseif (isset($data['topic']) && isset($data['resource'])) {
    $type = $data['topic'];
    if (preg_match('/\/(\d+)$/', $data['resource'], $m)) {
        $id = $m[1];
    }
}

if (preg_match('/(\d+)$/', $data['resource'], $m)) {
    $id = $m[1];
}

// =====================================================
// VALIDA TIPO
// =====================================================

$validTypes = ['payment', 'merchant_order'];

if (!in_array($type, $validTypes)) {
    logDebug('Tipo ignorado', ['type' => $type]);
    http_response_code(200); // evita reenvio
    exit;
}

// =====================================================
// PROCESSAMENTO
// =====================================================

if ($type === 'payment') {

    $paymentId = $id;

    logDebug('Processando pagamento', ['payment_id' => $paymentId]);

    $paymentInfo = getPaymentInfo($paymentId);

    if (!$paymentInfo) {
        logDebug('Erro ao consultar pagamento', ['payment_id' => $paymentId]);
        http_response_code(500);
        exit;
    }

    logDebug('Dados pagamento', $paymentInfo);

    if ($paymentInfo['status'] === 'approved') {
        processApprovedPayment($paymentInfo);
    } else {
        logDebug('Pagamento não aprovado', [
            'status' => $paymentInfo['status'] ?? null,
            'status_detail' => $paymentInfo['status_detail'] ?? null
        ]);
    }
}

// =====================================================
// RETORNO
// =====================================================

http_response_code(200);
echo json_encode(['success' => true]);
exit;

// =====================================================
// FUNÇÕES
// =====================================================

function getPaymentInfo($paymentId) {

    $url = "https://api.mercadopago.com/v1/payments/{$paymentId}";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . MP_ACCESS_TOKEN
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        logDebug('Erro API MP', [
            'http_code' => $httpCode,
            'response' => $response
        ]);
        return null;
    }

    return json_decode($response, true);
}

function processApprovedPayment($paymentInfo) {

    $externalReference = $paymentInfo['external_reference'] ?? null;
    $payerId = $paymentInfo['payer']['id'] ?? null;
    $payerEmail = $paymentInfo['payer']['email'] ?? null;
    $amount = $paymentInfo['transaction_amount'] ?? 0;
    $paymentId = $paymentInfo['id'];

    logDebug('Pagamento aprovado', [
        'payment_id' => $paymentId,
        'external_ref' => $externalReference,
        'amount' => $amount
    ]);

    $donations = loadJSON(DONATIONS_FILE, []);

    $found = false;

    foreach ($donations as &$donation) {
        if ($externalReference && $donation['id'] === $externalReference) {

            $donation['status'] = 'approved';
            $donation['payment_id'] = $paymentId;
            $donation['approved_at'] = date('c');
            $donation['payer_email'] = $payerEmail;
            $donation['payer_id'] = $payerId;
            $donation['final_amount'] = $amount;

            $found = true;

            logDebug('Doação atualizada', [
                'donation_id' => $donation['id']
            ]);

            break;
        }
    }

    if (!$found) {

        logDebug('Criando nova doação', [
            'external_ref' => $externalReference
        ]);

        $donations[] = [
            'id' => $externalReference ?? generateId('don'),
            'preference_id' => $paymentInfo['preference_id'] ?? null,
            'payment_id' => $paymentId,
            'amount' => $amount,
            'final_amount' => $amount,
            'email' => $payerEmail,
            'payer_email' => $payerEmail,
            'payer_id' => $payerId,
            'fingerprint' => null,
            'status' => 'approved',
            'created_at' => date('c'),
            'approved_at' => date('c')
        ];
    }

    if (saveJSON(DONATIONS_FILE, $donations)) {

        logDebug('Doações salvas');

        updateSupportersCount();

    } else {
        logDebug('Erro ao salvar doações');
    }
}

function updateSupportersCount() {

    $donations = loadJSON(DONATIONS_FILE, []);

    $approved = array_filter($donations, function ($d) {
        return isset($d['status']) && $d['status'] === 'approved';
    });

    $cache = [
        'total' => count($approved),
        'last_updated' => date('c')
    ];

    saveJSON(SUPPORTERS_COUNT_FILE, $cache);

    logDebug('Contador atualizado', ['total' => count($approved)]);
}