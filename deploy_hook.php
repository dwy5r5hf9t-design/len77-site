<?php
// Мгновенный перенос по сигналу из GitHub. Ключ лежит ВНЕ сайта.
$secretFile = '/home/a1329257/webhook_secret';
$deployCmd  = '/bin/bash /home/a1329257/len77_deploy.sh >> /home/a1329257/len77_deploy.log 2>&1';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('method'); }

$secret = trim(@file_get_contents($secretFile));
if ($secret === '') { http_response_code(500); exit('no secret'); }

$raw = file_get_contents('php://input');
$sig = isset($_SERVER['HTTP_X_HUB_SIGNATURE_256']) ? $_SERVER['HTTP_X_HUB_SIGNATURE_256'] : '';
$expect = 'sha256=' . hash_hmac('sha256', $raw, $secret);
if (!hash_equals($expect, $sig)) { http_response_code(403); exit('bad signature'); }

$data = json_decode($raw, true);
$ref = isset($data['ref']) ? $data['ref'] : '';
if ($ref !== 'refs/heads/main') { http_response_code(200); exit('ignored'); }

exec($deployCmd, $out, $code);
http_response_code($code === 0 ? 200 : 500);
exit($code === 0 ? 'deployed' : 'deploy failed');
