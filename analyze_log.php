<?php

$logFile = __DIR__ . '/logs/gateway.log';
if (!file_exists($logFile)) {
    die("Log file not found.");
}

$lines = file($logFile);
$total = count($lines);
$keys = [];
$errors = ['401' => 0, '429' => 0];

foreach ($lines as $line) {
    if (preg_match('/API Key: (.+?) - Path: .+? - Status: (\d+)/', $line, $matches)) {
        $key = $matches[1];
        $status = $matches[2];

        $keys[$key] = ($keys[$key] ?? 0) + 1;
        if (isset($errors[$status])) $errors[$status]++;
    }
}

echo "Total Requests: $total\n";
echo "Requests per API Key:\n";
foreach ($keys as $key => $count) {
    echo "  $key: $count\n";
}
echo "401 Errors: {$errors['401']}\n";
echo "429 Errors: {$errors['429']}\n";