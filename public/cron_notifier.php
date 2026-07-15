<?php
// cron_notifier.php

// Lokasi file kredensial Firebase
$credentialFile = __DIR__ . '/../firebase_credentials.json';
// Lokasi file state untuk melacak perubahan
$stateFile = __DIR__ . '/last_state.json';

// 1. Ambil data terbaru dari API Monitor
$ch = curl_init('https://tahajjud.quizb.my.id/api_monitor.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$apiResponse = curl_exec($ch);
curl_close($ch);

$currentData = json_decode($apiResponse, true);
if (!$currentData || !isset($currentData['data'])) {
    die("Gagal membaca API Monitor.\n");
}

$data = $currentData['data'];

$currentState = [
    'maktabah_search' => $data['maktabah']['search_count'] ?? 0,
    'maktabah_download' => $data['maktabah']['download_count'] ?? 0,
    'quizb_attempts' => $data['quizb']['attempt_count'] ?? 0,
    'wirid_events' => $data['wirid']['total_events'] ?? 0,
    'tahajjud_visits' => $data['tahajjud']['total_visits'] ?? 0
];

// 2. Baca state sebelumnya
$lastState = [];
if (file_exists($stateFile)) {
    $lastState = json_decode(file_get_contents($stateFile), true);
}

// 3. Bandingkan state
$messages = [];

if (isset($lastState['maktabah_search']) && $currentState['maktabah_search'] > $lastState['maktabah_search']) {
    $diff = $currentState['maktabah_search'] - $lastState['maktabah_search'];
    $messages[] = "Ada $diff pencarian Maktabah baru.";
}

if (isset($lastState['quizb_attempts']) && $currentState['quizb_attempts'] > $lastState['quizb_attempts']) {
    $diff = $currentState['quizb_attempts'] - $lastState['quizb_attempts'];
    $messages[] = "Ada $diff pengerjaan QuizB baru.";
}

if (isset($lastState['wirid_events']) && $currentState['wirid_events'] > $lastState['wirid_events']) {
    $diff = $currentState['wirid_events'] - $lastState['wirid_events'];
    $messages[] = "Ada $diff entri Wirid baru.";
}

// Update last state
file_put_contents($stateFile, json_encode($currentState));

// 4. Kirim notifikasi jika ada pesan baru
if (empty($messages)) {
    echo "Tidak ada data baru. State: " . json_encode($currentState) . "\n";
    exit;
}

$notificationBody = implode("\n", $messages);
sendFirebaseNotification("Aktivitas Baru Terdeteksi!", $notificationBody, $credentialFile);
echo "Notifikasi berhasil dikirim: \n$notificationBody\n";


/**
 * Fungsi untuk mengirim Push Notification FCM v1 menggunakan JWT murni (Tanpa library Google Client)
 */
function sendFirebaseNotification($title, $body, $credFile) {
    if (!file_exists($credFile)) {
        echo "Error: Kredensial Firebase tidak ditemukan.\n";
        return;
    }
    
    $jsonKey = json_decode(file_get_contents($credFile), true);
    $projectId = $jsonKey['project_id'];
    
    $token = getOauth2Token($jsonKey);
    if (!$token) {
        echo "Error: Gagal mendapatkan token Oauth2.\n";
        return;
    }
    
    $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
    
    $message = [
        "message" => [
            "topic" => "admin_alerts",
            "notification" => [
                "title" => $title,
                "body" => $body
            ],
            "android" => [
                "priority" => "high"
            ]
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result;
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function getOauth2Token($jsonKey) {
    $header = json_encode([
        'alg' => 'RS256',
        'typ' => 'JWT'
    ]);
    
    $now = time();
    $claimSet = json_encode([
        'iss' => $jsonKey['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => $jsonKey['token_uri'],
        'exp' => $now + 3600,
        'iat' => $now
    ]);
    
    $base64UrlHeader = base64url_encode($header);
    $base64UrlClaimSet = base64url_encode($claimSet);
    $signatureInput = $base64UrlHeader . '.' . $base64UrlClaimSet;
    
    $signature = '';
    openssl_sign($signatureInput, $signature, $jsonKey['private_key'], 'SHA256');
    $base64UrlSignature = base64url_encode($signature);
    
    $jwt = $signatureInput . '.' . $base64UrlSignature;
    
    $ch = curl_init($jsonKey['token_uri']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    return $data['access_token'] ?? null;
}
