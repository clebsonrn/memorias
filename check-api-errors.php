<?php
/**
 * Verificar se messages.php tem erros
 */

echo "<h2>🔍 Verificando API</h2>";
echo "<hr>";

$api_file = __DIR__ . '/api/messages.php';

if (!file_exists($api_file)) {
    die("❌ <strong>API não encontrada!</strong><br>Caminho esperado: $api_file");
}

echo "✅ Arquivo existe: <code>$api_file</code><br><br>";

// Testa sintaxe
echo "<h3>1️⃣ Teste de Sintaxe PHP:</h3>";
$output = [];
$return = 0;
exec("php -l " . escapeshellarg($api_file) . " 2>&1", $output, $return);

if ($return === 0) {
    echo "✅ <strong>Sintaxe OK!</strong><br>";
} else {
    echo "❌ <strong>Erro de sintaxe:</strong><br>";
    echo "<pre style='background: #fee; padding: 10px; color: #c00;'>";
    echo implode("\n", $output);
    echo "</pre>";
    die();
}

echo "<br>";

// Simula requisição PUT
echo "<h3>2️⃣ Simulando PUT Request:</h3>";

$_SERVER['REQUEST_METHOD'] = 'PUT';
$_SERVER['HTTP_X_ADMIN_TOKEN'] = '82941092900';

// Captura output e erros
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Simula input JSON
$stdin = fopen('php://memory', 'r+');
fwrite($stdin, json_encode([
    'message_id' => 'msg_teste_123',
    'reply' => 'Teste de resposta'
]));
rewind($stdin);

try {
    include $api_file;
    $output = ob_get_clean();
    
    echo "<strong>Resposta:</strong><br>";
    echo "<pre style='background: #f5f5f5; padding: 10px;'>";
    echo htmlspecialchars($output);
    echo "</pre>";
    
} catch (Throwable $e) {
    ob_end_clean();
    echo "❌ <strong>ERRO FATAL:</strong><br>";
    echo "<pre style='background: #fee; padding: 10px; color: #c00;'>";
    echo $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString();
    echo "</pre>";
}

fclose($stdin);
?>