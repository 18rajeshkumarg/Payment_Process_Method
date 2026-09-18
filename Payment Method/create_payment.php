<?php
/**
 * Create a PayMongo payment link and redirect the customer to the checkout page.
 */

$config = [
    'paymongo_secret_key' => getenv('PAYMONGO_SECRET_KEY') ?: '',
];

if (file_exists(__DIR__ . '/config.php')) {
    $local = require __DIR__ . '/config.php';
    if (is_array($local) && !empty($local['paymongo_secret_key'])) {
        $config['paymongo_secret_key'] = $local['paymongo_secret_key'];
    }
}

$secretKey = trim($config['paymongo_secret_key']);

function respondError(string $message, int $status = 400): void
{
    http_response_code($status);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">' .
        '<title>Payment Error</title><style>body{font-family:sans-serif;display:flex;justify-content:center;' .
        'align-items:center;height:100vh;background:#f4f4f6}div{background:#fff;padding:40px;border-radius:10px;' .
        'box-shadow:0 7px 29px rgba(100,100,111,.2);text-align:center}button{font-size:16px;padding:8px 20px;' .
        'background:#009039;border:none;color:#fff;border-radius:4px;cursor:pointer}</style></head><body><div>' .
        '<h2>Payment Error</h2><p>' . htmlspecialchars($message) . '</p>' .
        '<button onclick="history.back()">Go Back</button></div></body></html>';
    exit;
}

if (empty($secretKey) || strpos($secretKey, 'YOUR_') !== false) {
    respondError('PayMongo secret key is not configured. Set PAYMONGO_SECRET_KEY or edit config.php.');
}

if (!isset($_POST['amount'])) {
    respondError('Missing amount.');
}

$amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);
if ($amount === false || $amount < 100) {
    respondError('Please enter a valid amount of at least 100 PHP.');
}

$amountInCentavos = (int)round($amount * 100);

$data = [
    'data' => [
        'attributes' => [
            'amount'      => $amountInCentavos,
            'currency'    => 'PHP',
            'description' => 'Sample Description',
            'remarks'     => 'Sample Remarks',
        ],
    ],
];

$ch = curl_init('https://api.paymongo.com/v1/links');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Basic ' . base64_encode($secretKey . ':'),
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$result = curl_exec($ch);
$curlError = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

if ($result === false) {
    respondError('Could not reach PayMongo: ' . $curlError, 502);
}

$response = json_decode($result, true);

if (isset($response['data']['attributes']['checkout_url'])) {
    header('Location: ' . $response['data']['attributes']['checkout_url']);
    exit();
}

$errorMessage = 'PayMongo returned an error.';
if (!empty($response['errors'][0]['detail'])) {
    $errorMessage = htmlspecialchars($response['errors'][0]['detail']);
} elseif (!empty($response['message'])) {
    $errorMessage = htmlspecialchars($response['message']);
} else {
    $errorMessage .= ' HTTP ' . $httpCode;
}

respondError($errorMessage, 502);