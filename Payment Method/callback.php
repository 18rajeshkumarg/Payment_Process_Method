<?php
/**
 * PayMongo webhook callback handler.
 * Logs the outcome of each payment event.
 */

$input = file_get_contents('php://input');
$event = json_decode($input, true);

$logFile = __DIR__ . '/payments.log';

$status = $event['data']['attributes']['status'] ?? null;
$type = $event['data']['type'] ?? null;
$paymentId = $event['data']['id'] ?? 'unknown';

if ($type === 'payment' && $status === 'paid') {
    file_put_contents($logFile, date('c') . " Payment successful for ID: " . $paymentId . "\n", FILE_APPEND);
} else {
    $statusLabel = $status ?? 'unset';
    file_put_contents($logFile, date('c') . " Payment " . $statusLabel . " for ID: " . $paymentId . "\n", FILE_APPEND);
}

http_response_code(200);
echo 'OK';