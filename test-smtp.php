<?php
/**
 * Teste AWS SES SMTP
 */

$smtp_host = 'email-smtp.us-west-2.amazonaws.com';
$smtp_port = 587;
$smtp_user = 'AKIAIQXROUMGJ4UKAM5A';
$smtp_pass = 'AgBGLE5YUK9MOi69ey1mOk/+39Yq2ZUHp/vi7TeWJl8S';
$smtp_from = 'mkt@clebsonribeiro.com.br';
$test_email = $_GET['email'] ?? '';

if (empty($test_email)) {
    die('❌ Use: test-aws-ses.php?email=seu@email.com');
}

echo "<h2>🧪 Teste AWS SES</h2>";
echo "<p><strong>Host:</strong> $smtp_host</p>";
echo "<p><strong>Porta:</strong> $smtp_port</p>";
echo "<p><strong>Região:</strong> us-west-2 (Oregon)</p>";
echo "<hr>";

// Conecta
$socket = @fsockopen($smtp_host, $smtp_port, $errno, $errstr, 10);

if (!$socket) {
    die("❌ Conexão falhou: $errstr");
}

echo "✅ Conectado ao AWS SES!<br>";

// Resposta inicial
$r = fgets($socket);
echo "Resposta: <code>$r</code><br>";

// EHLO
fputs($socket, "EHLO $smtp_host\r\n");
echo "<br><strong>→ EHLO</strong><br>";
while ($line = fgets($socket)) {
    echo "<code>$line</code><br>";
    if (substr($line, 3, 1) == ' ') break;
}

// STARTTLS
echo "<br><strong>→ STARTTLS</strong><br>";
fputs($socket, "STARTTLS\r\n");
$tls = fgets($socket);
echo "<code>$tls</code><br>";

if (strpos($tls, '220') === false) {
    echo "❌ STARTTLS falhou<br>";
    fclose($socket);
    die();
}

// Ativa TLS
$crypto = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

if (!$crypto) {
    echo "❌ TLS falhou<br>";
    fclose($socket);
    die();
}

echo "✅ TLS ativado!<br>";

// EHLO após TLS
fputs($socket, "EHLO $smtp_host\r\n");
echo "<br><strong>→ EHLO após TLS</strong><br>";
while ($line = fgets($socket)) {
    echo "<code>$line</code><br>";
    if (substr($line, 3, 1) == ' ') break;
}

// AUTH LOGIN
echo "<br><strong>→ AUTH LOGIN</strong><br>";
fputs($socket, "AUTH LOGIN\r\n");
$a1 = fgets($socket);
echo "<code>$a1</code><br>";

fputs($socket, base64_encode($smtp_user) . "\r\n");
$a2 = fgets($socket);
echo "<code>$a2</code><br>";

fputs($socket, base64_encode($smtp_pass) . "\r\n");
$a3 = fgets($socket);
echo "<code>$a3</code><br>";

if (strpos($a3, '235') === false) {
    echo "❌ <strong>Autenticação falhou!</strong><br>";
    echo "<p>⚠️ <strong>IMPORTANTE:</strong> Verifique se o email <code>$smtp_from</code> está <strong>verificado</strong> no AWS SES.</p>";
    echo "<p>AWS SES só permite enviar de emails/domínios verificados.</p>";
    fclose($socket);
    die();
}

echo "✅ Autenticado no AWS SES!<br>";

// MAIL FROM
echo "<br><strong>→ MAIL FROM</strong><br>";
fputs($socket, "MAIL FROM: <$smtp_from>\r\n");
$m = fgets($socket);
echo "<code>$m</code><br>";

// RCPT TO
echo "<br><strong>→ RCPT TO</strong><br>";
fputs($socket, "RCPT TO: <$test_email>\r\n");
$rt = fgets($socket);
echo "<code>$rt</code><br>";

if (strpos($rt, '250') === false) {
    echo "❌ <strong>RCPT TO falhou!</strong><br>";
    echo "<p>AWS SES em <strong>sandbox mode</strong> só permite enviar para emails verificados.</p>";
    echo "<p>Para enviar para qualquer email, precisa <a href='https://docs.aws.amazon.com/ses/latest/dg/request-production-access.html' target='_blank'>sair do sandbox</a>.</p>";
    fclose($socket);
    die();
}

// DATA
echo "<br><strong>→ DATA</strong><br>";
fputs($socket, "DATA\r\n");
$d1 = fgets($socket);
echo "<code>$d1</code><br>";

// Email
$msg = "From: Clebson Ribeiro - Memorias <$smtp_from>\r\n";
$msg .= "To: <$test_email>\r\n";
$msg .= "Subject: =?UTF-8?B?" . base64_encode("🎵 Teste AWS SES - Memórias de um Pé Vermelho") . "?=\r\n";
$msg .= "MIME-Version: 1.0\r\n";
$msg .= "Content-Type: text/html; charset=UTF-8\r\n";
$msg .= "\r\n";
$msg .= "<div style='font-family: Georgia, serif; max-width: 600px; margin: 0 auto;'>\n";
$msg .= "<h1 style='color: #ff7820;'>✅ AWS SES Funcionando!</h1>\n";
$msg .= "<p>Email enviado com sucesso via <strong>Amazon SES (us-west-2)</strong></p>\n";
$msg .= "<h3>Configuração:</h3>\n";
$msg .= "<ul>\n";
$msg .= "<li><strong>Host:</strong> email-smtp.us-west-2.amazonaws.com</li>\n";
$msg .= "<li><strong>Porta:</strong> 587 (STARTTLS)</li>\n";
$msg .= "<li><strong>Região:</strong> Oregon (us-west-2)</li>\n";
$msg .= "</ul>\n";
$msg .= "<p style='color: green;'><strong>✅ AWS SES configurado e funcionando!</strong></p>\n";
$msg .= "</div>\n";
$msg .= "\r\n.\r\n";

fputs($socket, $msg);
$d2 = fgets($socket);
echo "<code>$d2</code><br>";

if (strpos($d2, '250') !== false) {
    echo "<br><h2 style='color: green;'>✅ EMAIL ENVIADO COM SUCESSO VIA AWS SES!</h2>";
    echo "<p>Verifique: <strong>$test_email</strong></p>";
    echo "<hr>";
    echo "<h3>📋 Configuração Final:</h3>";
    echo "<pre style='background: #e8f5e9; padding: 15px; border-radius: 5px; border-left: 3px solid green;'>";
    echo "SMTP_HOST: email-smtp.us-west-2.amazonaws.com\n";
    echo "SMTP_PORT: 587\n";
    echo "SMTP_USER: AKIAIQXROUMGJ4UKAM5A\n";
    echo "SMTP_PASS: AgBGLE5YUK9MOi69ey1mOk/+39Yq2ZUHp/vi7TeWJl8S\n";
    echo "SMTP_FROM: mkt@clebsonribeiro.com.br\n";
    echo "TLS: STARTTLS (obrigatório)\n";
    echo "</pre>";
    echo "<p><strong>✅ Vou atualizar a API com AWS SES agora!</strong></p>";
    
    echo "<hr>";
    echo "<h3>💡 Dicas AWS SES:</h3>";
    echo "<ul>";
    echo "<li><strong>Sandbox Mode:</strong> Só envia para emails verificados. <a href='https://us-west-2.console.aws.amazon.com/ses/home?region=us-west-2#/account' target='_blank'>Verificar</a></li>";
    echo "<li><strong>Produção:</strong> <a href='https://docs.aws.amazon.com/ses/latest/dg/request-production-access.html' target='_blank'>Pedir saída do sandbox</a> para enviar para qualquer email</li>";
    echo "<li><strong>Limite:</strong> 200 emails/dia em sandbox, depois 50.000/dia</li>";
    echo "</ul>";
} else {
    echo "<br>❌ Erro ao enviar: $d2<br>";
}

fputs($socket, "QUIT\r\n");
fclose($socket);
?>