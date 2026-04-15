<?php

// ===== CONFIG =====
$secret = '19921705windows'; // aceeași ca în GitHub
$repoPath = '/var/www/proiectul-tau'; // path proiect server

// ===== LOG FUNCTION =====
function logMessage($msg) {
    file_put_contents(__DIR__ . '/deploy.log', date('Y-m-d H:i:s') . " - " . $msg . PHP_EOL, FILE_APPEND);
}

// ===== GET INPUT =====
$payload = file_get_contents('php://input');
$headers = getallheaders();

// ===== VERIFY SIGNATURE =====
$signature = $headers['X-Hub-Signature-256'] ?? '';

if (!$signature) {
    logMessage("No signature");
    http_response_code(403);
    exit('No signature');
}

$expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (!hash_equals($expected, $signature)) {
    logMessage("Invalid signature");
    http_response_code(403);
    exit('Invalid signature');
}

// ===== PARSE JSON =====
$data = json_decode($payload, true);

if (!$data) {
    logMessage("Invalid JSON");
    exit('Invalid JSON');
}

// ===== CHECK BRANCH =====
$branch = $data['ref'] ?? '';

if ($branch !== 'refs/heads/main') {
    logMessage("Not main branch: " . $branch);
    exit('Not main branch');
}

logMessage("Deploy triggered");

// ===== EXECUTE DEPLOY =====
$commands = [
    "cd $repoPath",
    "git reset --hard",
    "git pull origin main 2>&1"
];

$output = [];

foreach ($commands as $cmd) {
    exec($cmd, $cmdOutput);
    $output[] = ">>> $cmd";
    $output = array_merge($output, $cmdOutput);
}

// ===== SAVE LOG =====
logMessage("Deploy finished");

// ===== OUTPUT =====
echo "<pre>";
echo implode("\n", $output);
echo "</pre>";