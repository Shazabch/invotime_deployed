<?php
// test_fcm_v1.php - FCM HTTP v1 API Tester (Working in December 2025)
// Access via browser: http://yourdomain.com/test_fcm_v1.php

// ================== CONFIGURATION ==================
// Path to your service account JSON file (in root)
$service_account_file = __DIR__ . '/invotime-399613-firebase-adminsdk-qlq1j-61727bd060.json';

// Your Firebase Project ID (from the JSON file: look for "project_id")
$project_id = 'invotime-399613';  // <<<--- Usually the part before -firebase-adminsdk in filename

// A real FCM registration token from your mobile app (from employees.fcm_token in DB)
$TEST_DEVICE_TOKEN = 'dFGm_qUoQCeP5h41yAZ_Bi:APA91bGzBSzJebdp4cwRerl896DK5eflqdjl_iJO1I0d0_TXa_cXnWOwZY6b2naHuRyqdenxuM3ayUCVIgiGF0kN0EvtVC6La04sifXkQdEbnCymAhUf5-c';  // <<<--- REPLACE WITH A VALID TOKEN FROM YOUR APP

$TEST_TITLE = 'FCM v1 Test Success!';
$TEST_BODY  = 'Congratulations! FCM HTTP v1 is now working on your server (Dec 2025). 🎉';
// ===================================================

if (!file_exists($service_account_file)) {
    die("<h2 style='color:red;'>ERROR: Service account file not found at: $service_account_file</h2>");
}

if ($project_id === 'invotime-9fbee' && strpos($service_account_file, 'invotime-9fbee') === false) {
    die("<h2 style='color:red;'>ERROR: Confirm your \$project_id from the JSON file (open it and look for \"project_id\")</h2>");
}

if ($TEST_DEVICE_TOKEN === 'PUT_REAL_TOKEN_HERE' || empty($TEST_DEVICE_TOKEN)) {
    die("<h2 style='color:red;'>ERROR: Please replace \$TEST_DEVICE_TOKEN with a real token from your mobile app.</h2>");
}

// Function to generate OAuth 2.0 access token
function getAccessToken($json_file) {
    $json = json_decode(file_get_contents($json_file), true);
    if (!$json) {
        return null;
    }

    $header = ['alg' => 'RS256', 'typ' => 'JWT'];
    $claim = [
        'iss' => $json['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => 'https://oauth2.googleapis.com/token',
        'exp' => time() + 3600,
        'iat' => time()
    ];

    $encoded_header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($header)));
    $encoded_claim = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($claim)));

    $signature_input = "$encoded_header.$encoded_claim";

    openssl_sign($signature_input, $signature, $json['private_key'], OPENSSL_ALGO_SHA256);
    $encoded_signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    $jwt = "$encoded_header.$encoded_claim.$encoded_signature";

    $post_data = http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ]);

    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    return $data['access_token'] ?? null;
}

$access_token = getAccessToken($service_account_file);

if (!$access_token) {
    die("<h2 style='color:red;'>ERROR: Failed to generate access token. Check if OpenSSL is enabled on your server and JSON is valid.</h2>");
}

// Payload for single device
$message = [
    'message' => [
        'token' => $TEST_DEVICE_TOKEN,
        'notification' => [
            'title' => $TEST_TITLE,
            'body' => $TEST_BODY
        ],
        'data' => [
            'test' => 'v1_working',
            'time' => date('Y-m-d H:i:s')
        ]
    ]
];

$headers = [
    'Authorization: Bearer ' . $access_token,
    'Content-Type: application/json'
];

echo "<h2>FCM HTTP v1 Test (December 2025)</h2>";
echo "<pre>";
echo "Project ID: $project_id\n";
echo "Token (preview): " . substr($TEST_DEVICE_TOKEN, 0, 40) . "...\n\n";

$ch = curl_init("https://fcm.googleapis.com/v1/projects/$project_id/messages:send");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Remove in production
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $http_code\n";
echo "Response:\n" . htmlspecialchars($response) . "\n\n";

if ($curl_error) {
    echo "<span style='color:red;'>cURL Error: $curl_error</span>\n";
} elseif ($http_code === 200) {
    echo "<h3 style='color:green;'>SUCCESS! Notification sent via FCM v1 API!</h3>";
    echo "You should see the notification on your phone now.\n";
} elseif ($http_code === 404) {
    echo "<h3 style='color:orange;'>Token invalid (UNREGISTERED or not found)</h3>";
    echo "The device token is no longer valid (app uninstalled or token refreshed).\n";
} elseif ($http_code === 401) {
    echo "<h3 style='color:red;'>Unauthorized - Access token issue</h3>";
} else {
    echo "<h3 style='color:red;'>Error HTTP $http_code</h3>";
}

echo "</pre>";
echo "<small>Completed at " . date('Y-m-d H:i:s') . "</small>";
?>