<?php
$logFile = '/home/a1329257/lenbot_logs/webhook_debug.log';
file_put_contents($logFile, "=== " . date('H:i:s') . " Hook received ===\n", FILE_APPEND);

$secret = trim(@file_get_contents('/home/a1329257/webhook_secret'));
$raw = file_get_contents('php://input');
$sig = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

if ('sha256=' . hash_hmac('sha256', $raw, $secret) !== $sig) {
    file_put_contents($logFile, "Bad signature\n", FILE_APPEND);
    http_response_code(403); exit('bad sig');
}

$cmd = '/bin/bash /home/a1329257/lenbot_deploy.sh 2>&1';
exec($cmd, $out, $code);

file_put_contents($logFile, "Bash output:\n" . implode("\n", $out) . "\nExit code: $code\n", FILE_APPEND);
http_response_code(200); exit('ok');
