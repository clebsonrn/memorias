<?php
/**
 * API de Mensagens v4 - Memórias de um Pé Vermelho
 * + File locking para concorrência
 * + Sistema de respostas do artista
 * + Notificação por email quando responder
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://memorias.clebsonribeiro.com.br');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

// ============================================================
// CONFIGURAÇÕES
// ============================================================

define('RECAPTCHA_SECRET',  '6Lc5im0sAAAAALq78aiXvG_ToBTWhDjy94JinCU9');
define('RECAPTCHA_MIN_SCORE', 0.5);
define('RATE_LIMIT_HOURS', 3);
define('MESSAGES_PER_PAGE', 10);
define('MAX_CHARS', 280);
define('MIN_CHARS', 10);
define('DATA_FILE', __DIR__ . '/messages.json');
define('RATE_FILE', __DIR__ . '/rate_limit.json');

// Email para notificações
define('ARTIST_EMAIL', 'contato@clebsonribeiro.com.br');
define('ARTIST_NAME',  'Clebson Ribeiro');

// Token admin (para responder mensagens) ← CORRIGIDO!
define('ADMIN_TOKEN', '82941092900');

// Configuração SMTP - AWS SES
define('SMTP_HOST',     'email-smtp.us-west-2.amazonaws.com');
define('SMTP_PORT',     587);
define('SMTP_USER',     'AKIAIQXROUMGJ4UKAM5A');
define('SMTP_PASS',     'AgBGLE5YUK9MOi69ey1mOk/+39Yq2ZUHp/vi7TeWJl8S');
define('SMTP_FROM',     'mkt@clebsonribeiro.com.br');
define('SMTP_FROM_NAME', 'Clebson Ribeiro - Memórias de um Pé Vermelho');

// ============================================================
// FILTRO DE PALAVRÕES
// ============================================================

$badwords = [
    'merda', 'porra', 'caralho', 'buceta', 'foder', 'fodase',
    'puta', 'viado', 'cuzão', 'cú', 'cu', 'desgraça', 'filha da puta',
    'filho da puta', 'fdp', 'vadia', 'arrombado', 'babaca',
    'idiota', 'imbecil', 'retardado', 'otário', 'lixo',
];

function filterBadWords(string $text, array $badwords): string {
    foreach ($badwords as $word) {
        $stars = str_repeat('*', mb_strlen($word));
        $text = preg_replace('/\b' . preg_quote($word, '/') . '\b/iu', $stars, $text);
    }
    return $text;
}

// ============================================================
// HELPERS
// ============================================================

function getIP(): string {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['HTTP_CLIENT_IP']
        ?? $_SERVER['REMOTE_ADDR']
        ?? '0.0.0.0';
    return trim(explode(',', $ip)[0]);
}

function hashIP(string $ip): string {
    return hash('sha256', $ip . 'memorias_salt_2026');
}

/**
 * Carrega JSON com file locking
 * Retry até 3x se arquivo estiver travado
 */
function loadJSON(string $file, $default, int $retries = 3) {
    if (!file_exists($file)) return $default;
    
    $attempt = 0;
    while ($attempt < $retries) {
        $fp = @fopen($file, 'r');
        if (!$fp) {
            $attempt++;
            usleep(100000); // 0.1s
            continue;
        }
        
        if (flock($fp, LOCK_SH)) {
            $content = fread($fp, filesize($file) ?: 1);
            flock($fp, LOCK_UN);
            fclose($fp);
            
            $data = json_decode($content, true);
            return $data ?? $default;
        }
        
        fclose($fp);
        $attempt++;
        usleep(100000);
    }
    
    return $default;
}

/**
 * Salva JSON com file locking
 * Retry até 3x se arquivo estiver travado
 */
function saveJSON(string $file, $data, int $retries = 3): bool {
    $attempt = 0;
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    while ($attempt < $retries) {
        $fp = @fopen($file, 'c');
        if (!$fp) {
            $attempt++;
            usleep(100000);
            continue;
        }
        
        if (flock($fp, LOCK_EX)) {
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, $json);
            fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);
            return true;
        }
        
        fclose($fp);
        $attempt++;
        usleep(100000);
    }
    
    return false;
}

function respond(bool $success, $data = [], int $code = 200): void {
    http_response_code($code);
    echo json_encode(array_merge(['success' => $success], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================================
// ENVIO DE EMAIL
// ============================================================

function sendReplyNotification(string $to, string $userName, string $userMessage, string $reply): bool {
    if (empty($to)) return false;
    
    // Conecta ao AWS SES
    $socket = @fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 10);
    if (!$socket) return false;
    
    fgets($socket); // 220
    
    // EHLO
    fputs($socket, "EHLO " . SMTP_HOST . "\r\n");
    while ($line = fgets($socket)) {
        if (substr($line, 3, 1) == ' ') break;
    }
    
    // STARTTLS
    fputs($socket, "STARTTLS\r\n");
    fgets($socket); // 220
    
    if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        fclose($socket);
        return false;
    }
    
    // EHLO após TLS
    fputs($socket, "EHLO " . SMTP_HOST . "\r\n");
    while ($line = fgets($socket)) {
        if (substr($line, 3, 1) == ' ') break;
    }
    
    // AUTH LOGIN
    fputs($socket, "AUTH LOGIN\r\n");
    fgets($socket);
    fputs($socket, base64_encode(SMTP_USER) . "\r\n");
    fgets($socket);
    fputs($socket, base64_encode(SMTP_PASS) . "\r\n");
    $auth = fgets($socket);
    
    if (strpos($auth, '235') === false) {
        fclose($socket);
        return false;
    }
    
    // MAIL FROM
    fputs($socket, "MAIL FROM: <" . SMTP_FROM . ">\r\n");
    fgets($socket);
    
    // RCPT TO
    fputs($socket, "RCPT TO: <$to>\r\n");
    fgets($socket);
    
    // DATA
    fputs($socket, "DATA\r\n");
    fgets($socket);
    
    // Monta email HTML
    $htmlMessage = "<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'></head>
<body style='font-family: Georgia, serif; background: #0a0a0a; color: #d4c5b0; padding: 40px 20px;'>
    <div style='max-width: 600px; margin: 0 auto; background: rgba(20, 15, 10, 0.9); border: 1px solid rgba(200, 170, 120, 0.2); border-radius: 15px; padding: 40px;'>
        
        <h1 style='font-family: Brush Script MT, cursive; font-size: 32px; color: #f4e4c1; text-align: center; margin-bottom: 10px;'>Memórias de um Pé Vermelho</h1>
        
        <p style='text-align: center; color: #c9b896; font-size: 16px; margin-bottom: 30px;'>Clebson Ribeiro</p>
        
        <div style='border-left: 3px solid rgba(255, 120, 30, 0.5); padding: 20px; background: rgba(10, 10, 10, 0.5); border-radius: 8px; margin-bottom: 30px;'>
            <p style='font-size: 14px; color: #8a7a6a; margin-bottom: 10px;'>Sua mensagem:</p>
            <p style='font-size: 16px; color: #d4c5b0; font-style: italic; line-height: 1.6;'>\"" . htmlspecialchars($userMessage) . "\"</p>
        </div>
        
        <div style='background: rgba(40, 30, 20, 0.6); border-left: 3px solid #ff7820; padding: 20px; border-radius: 8px; margin-bottom: 30px;'>
            <p style='font-size: 14px; color: #ff7820; margin-bottom: 10px; font-weight: bold;'>🎵 Resposta de Clebson:</p>
            <p style='font-size: 16px; color: #f4e4c1; line-height: 1.7;'>" . nl2br(htmlspecialchars($reply)) . "</p>
        </div>
        
        <div style='text-align: center; margin-top: 40px;'>
            <a href='https://memorias.clebsonribeiro.com.br' style='display: inline-block; background: linear-gradient(135deg, #ff7820, #ffa040); color: #fff; text-decoration: none; padding: 15px 35px; border-radius: 25px; font-weight: bold;'>🎧 Ouvir o Álbum</a>
        </div>
        
        <p style='text-align: center; font-size: 12px; color: #6a5a4a; margin-top: 30px; line-height: 1.6;'>
            Você recebeu este e-mail porque deixou uma mensagem no EPK de Memórias de um Pé Vermelho.<br>
            <a href='https://clebsonribeiro.com.br' style='color: #8a7a6a;'>clebsonribeiro.com.br</a>
        </p>
        
    </div>
</body>
</html>";
    
    // Envia email
    $email = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM . ">\r\n";
    $email .= "To: <$to>\r\n";
    $email .= "Subject: =?UTF-8?B?" . base64_encode("🎵 Clebson Ribeiro respondeu sua mensagem") . "?=\r\n";
    $email .= "Reply-To: " . ARTIST_EMAIL . "\r\n";
    $email .= "MIME-Version: 1.0\r\n";
    $email .= "Content-Type: text/html; charset=UTF-8\r\n";
    $email .= "\r\n";
    $email .= $htmlMessage . "\r\n";
    $email .= ".\r\n";
    
    fputs($socket, $email);
    $result = fgets($socket);
    
    fputs($socket, "QUIT\r\n");
    fclose($socket);
    
    return strpos($result, '250') !== false;
}

// ============================================================
// RECAPTCHA
// ============================================================

function verifyRecaptcha(string $token): bool {
    $response = file_get_contents(
        'https://www.google.com/recaptcha/api/siteverify',
        false,
        stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query([
                    'secret'   => RECAPTCHA_SECRET,
                    'response' => $token,
                    'remoteip' => getIP(),
                ]),
                'timeout' => 5,
            ]
        ])
    );

    if (!$response) return false;

    $result = json_decode($response, true);

    return isset($result['success'])
        && $result['success'] === true
        && isset($result['score'])
        && $result['score'] >= RECAPTCHA_MIN_SCORE;
}

// ============================================================
// RATE LIMITING
// ============================================================

function checkRateLimit(string $ipHash): bool {
    $rates = loadJSON(RATE_FILE, []);
    $now   = time();
    $limit = RATE_LIMIT_HOURS * 3600;

    $rates = array_filter($rates, fn($ts) => ($now - $ts) < $limit);

    if (isset($rates[$ipHash])) {
        $wait = $limit - ($now - $rates[$ipHash]);
        $horas = floor($wait / 3600);
        $mins  = floor(($wait % 3600) / 60);
        respond(false, [
            'error'       => 'rate_limit',
            'message'     => "Você já enviou uma mensagem recentemente. Aguarde {$horas}h {$mins}min.",
            'retry_after' => $wait,
        ], 429);
    }

    return true;
}

function registerRateLimit(string $ipHash): void {
    $rates = loadJSON(RATE_FILE, []);
    $now   = time();
    $limit = RATE_LIMIT_HOURS * 3600;

    $rates = array_filter($rates, fn($ts) => ($now - $ts) < $limit);
    $rates[$ipHash] = $now;
    saveJSON(RATE_FILE, $rates);
}

// ============================================================
// ROTAS
// ============================================================

$method = $_SERVER['REQUEST_METHOD'];

// GET — lista paginada (pública)
if ($method === 'GET') {
    $page     = max(1, (int)($_GET['page'] ?? 1));
    $messages = loadJSON(DATA_FILE, []);

    $messages = array_reverse($messages);

    $total    = count($messages);
    $pages    = max(1, (int)ceil($total / MESSAGES_PER_PAGE));
    $offset   = ($page - 1) * MESSAGES_PER_PAGE;
    $slice    = array_slice($messages, $offset, MESSAGES_PER_PAGE);

    // Remove campos privados + inclui resposta se houver
    $public = array_map(function($m) {
        $result = [
            'id'      => $m['id'],
            'name'    => $m['name'],
            'message' => $m['message'],
            'date'    => $m['date'],
        ];
        
        // Adiciona resposta se existir
        if (!empty($m['reply'])) {
            $result['reply'] = $m['reply'];
        }
        
        return $result;

    }, $slice);

    respond(true, [
        'messages'     => array_values($public),
        'total'        => $total,
        'page'         => $page,
        'pages'        => $pages,
        'per_page'     => MESSAGES_PER_PAGE,
    ]);
}

// POST — nova mensagem
if ($method === 'POST') {
    global $badwords;

    $input = json_decode(file_get_contents('php://input'), true);

    if (!empty($input['website'])) {
        respond(false, ['error' => 'spam'], 400);
    }

    $name     = trim(strip_tags($input['name']            ?? ''));
    $message  = trim(strip_tags($input['message']         ?? ''));
    $email    = trim(strip_tags($input['email']           ?? ''));
    $whatsapp = trim(strip_tags($input['whatsapp']        ?? ''));
    $consent  = !empty($input['consent']);
    $token    = trim($input['recaptcha_token']            ?? '');

    if (empty($name) || empty($message) || empty($token)) {
        respond(false, ['error' => 'missing_fields', 'message' => 'Preencha todos os campos obrigatórios.'], 400);
    }

    if (mb_strlen($name) > 50) {
        respond(false, ['error' => 'invalid', 'message' => 'Nome muito longo.'], 400);
    }

    if (mb_strlen($message) < MIN_CHARS) {
        respond(false, ['error' => 'invalid', 'message' => 'Mensagem muito curta. Mínimo ' . MIN_CHARS . ' caracteres.'], 400);
    }

    if (mb_strlen($message) > MAX_CHARS) {
        respond(false, ['error' => 'invalid', 'message' => 'Mensagem muito longa. Máximo ' . MAX_CHARS . ' caracteres.'], 400);
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(false, ['error' => 'invalid', 'message' => 'E-mail inválido.'], 400);
    }

    if (!empty($whatsapp)) {
        $whatsappDigits = preg_replace('/\D/', '', $whatsapp);
        if (strlen($whatsappDigits) < 10 || strlen($whatsappDigits) > 11) {
            respond(false, ['error' => 'invalid', 'message' => 'WhatsApp inválido.'], 400);
        }
        $whatsapp = $whatsappDigits;
    }

    $ipHash = hashIP(getIP());
    checkRateLimit($ipHash);

    if (!verifyRecaptcha($token)) {
        respond(false, ['error' => 'recaptcha', 'message' => 'Verificação de segurança falhou. Tente novamente.'], 400);
    }

    $name    = filterBadWords($name, $badwords);
    $message = filterBadWords($message, $badwords);

    $messages = loadJSON(DATA_FILE, []);

    $messages[] = [
        'id'       => uniqid('msg_', true),
        'name'     => $name,
        'message'  => $message,
        'date'     => date('c'),
        'ip'       => $ipHash,
        'reply'    => null,  // Resposta do artista
        '_email'   => $email,
        '_whatsapp'=> $whatsapp,
        '_consent' => $consent,
    ];

    if (!saveJSON(DATA_FILE, $messages)) {
        respond(false, ['error' => 'server', 'message' => 'Erro ao salvar. Tente novamente.'], 500);
    }
    
    registerRateLimit($ipHash);

    respond(true, [
        'message' => 'Mensagem enviada com sucesso!',
        'total'   => count($messages),
    ], 201);
}

// PUT — adicionar resposta (admin only)
if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $token     = $_SERVER['HTTP_X_ADMIN_TOKEN'] ?? $input['admin_token'] ?? '';
    $messageId = $input['message_id'] ?? '';
    $replyText = trim(strip_tags($input['reply'] ?? ''));
    
    // Valida token admin
    if ($token !== ADMIN_TOKEN) {
        respond(false, ['error' => 'unauthorized'], 403);
    }
    
    if (empty($messageId) || empty($replyText)) {
        respond(false, ['error' => 'missing_fields'], 400);
    }
    
    if (mb_strlen($replyText) > 500) {
        respond(false, ['error' => 'invalid', 'message' => 'Resposta muito longa. Máximo 500 caracteres.'], 400);
    }
    
    $messages = loadJSON(DATA_FILE, []);
    $found = false;
    
    foreach ($messages as &$msg) {
        if ($msg['id'] === $messageId) {
            $msg['reply'] = [
                'text' => $replyText,
                'date' => date('c'),
            ];
            $found = true;
            
            // Tenta enviar email se tiver
            $emailSent = false;
            if (!empty($msg['_email'])) {
                $emailSent = sendReplyNotification(
                    $msg['_email'],
                    $msg['name'],
                    $msg['message'],
                    $replyText
                );
            }
            
            // Debug: salva status do email
            $msg['_email_sent'] = $emailSent;
            $msg['_email_attempted'] = !empty($msg['_email']);
            
            break;
        }
    }
    
    if (!$found) {
        respond(false, ['error' => 'not_found', 'message' => 'Mensagem não encontrada.'], 404);
    }
    
    if (!saveJSON(DATA_FILE, $messages)) {
        respond(false, ['error' => 'server', 'message' => 'Erro ao salvar. Tente novamente.'], 500);
    }
    
    $responseData = ['message' => 'Resposta adicionada com sucesso!'];
    
    // Adiciona info sobre email
    if (isset($emailSent)) {
        $responseData['email_sent'] = $emailSent;
        $responseData['email_attempted'] = !empty($msg['_email']);
        if (!$emailSent && !empty($msg['_email'])) {
            $responseData['email_warning'] = 'Resposta salva, mas email não foi enviado. Verifique configuração SMTP.';
        }
    }
    
    respond(true, $responseData);
}

// Método não suportado
respond(false, ['error' => 'method_not_allowed'], 405);