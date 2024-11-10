<?php
// Получите данные из формы
$amount = floatval($_POST['amount']);  // Сумма доната

// Укажите ваши client_id и client_secret
$client_id = 'team094';
$client_secret = 'YzIQ1ScSQAO0eT6YYxwSWNxRoM8aVavM';
$auth_url = 'https://auth.bankingapi.ru/auth/realms/kubernetes/protocol/openid-connect/token';
$order_url = 'https://api.paymentgateway.ru/v1/orders';

// Получение access_token
function getAccessToken($client_id, $client_secret, $auth_url) {
    $data = [
        'grant_type' => 'client_credentials',
        'client_id' => $client_id,
        'client_secret' => $client_secret,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $auth_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    $response_data = json_decode($response, true);
    
    return $response_data['access_token'] ?? null;
}

$access_token = getAccessToken($client_id, $client_secret, $auth_url);

if (!$access_token) {
    die('Ошибка при получении access_token');
}

// Создание ордера
$order_data = [
    "orderId" => "DONATE" . time(),
    "orderName" => "Донат на проект",
    "amount" => [
        "value" => $amount,
        "code" => "RUB"
    ],
    "expire" => date(DATE_ISO8601, strtotime("+20 minutes")),
    "returnUrl" => "https://yourwebsite.com/thankyou", // URL возврата после оплаты
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $order_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $access_token,
    'Content-Type: application/json',
    'X-IBM-Client-Id: ' . $client_id,
]);

$response = curl_exec($ch);
curl_close($ch);
$response_data = json_decode($response, true);

if (isset($response_data['object']['payUrl'])) {
    header("Location: " . $response_data['object']['payUrl']);
    exit;
} else {
    echo "Ошибка при создании ордера. Пожалуйста, попробуйте позже.";
}
