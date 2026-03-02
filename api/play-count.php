<?php
/**
 * API de Contagem de Reproduções
 * Memórias de um Pé Vermelho
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Arquivo de dados
$dataFile = __DIR__ . '/play-counts.json';

// Inicializa arquivo se não existir
if (!file_exists($dataFile)) {
    $initialData = [
        '0' => 0,
        '1' => 0,
        '2' => 0,
        '3' => 0,
        '4' => 0,
        '5' => 0,
        '6' => 0,
        '7' => 0,
        '8' => 0,
        '9' => 0
    ];
    file_put_contents($dataFile, json_encode($initialData, JSON_PRETTY_PRINT));
}

// Lê dados atuais
$data = json_decode(file_get_contents($dataFile), true);

// GET - Retorna contagens
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'success' => true,
        'counts' => $data
    ]);
    exit;
}

// POST - Registra nova reprodução
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $trackId = isset($input['trackId']) ? (string)$input['trackId'] : null;
    
    if ($trackId === null || !isset($data[$trackId])) {
        echo json_encode([
            'success' => false,
            'error' => 'Invalid track ID'
        ]);
        exit;
    }
    
    // Incrementa contador
    $data[$trackId]++;
    
    // Salva
    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
    
    echo json_encode([
        'success' => true,
        'trackId' => $trackId,
        'count' => $data[$trackId],
        'allCounts' => $data
    ]);
    exit;
}

// Método não suportado
http_response_code(405);
echo json_encode([
    'success' => false,
    'error' => 'Method not allowed'
]);