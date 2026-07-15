<?php
// api_notify.php

// Token rahasia agar tidak ada orang luar yang bisa spam notifikasi
$secretToken = 'QUIZB_NOTIFY_SECRET_99';

// Menerima input JSON atau form URL encoded
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $secret = $_POST['secret'] ?? '';
    $message = $_POST['message'] ?? '';
    
    // Jika dikirim via JSON raw body
    if (empty($secret)) {
        $json = json_decode(file_get_contents('php://input'), true);
        $secret = $json['secret'] ?? '';
        $message = $json['message'] ?? '';
    }

    if ($secret !== $secretToken) {
        http_response_code(403);
        die(json_encode(["status" => "error", "message" => "Unauthorized"]));
    }

    if (empty($message)) {
        http_response_code(400);
        die(json_encode(["status" => "error", "message" => "Message is empty"]));
    }

    // Panggil fungsi sendFirebaseNotification dari cron_notifier.php
    $credentialFile = __DIR__ . '/../firebase_credentials.json';
    
    // Cegah cron_notifier.php mengeksekusi script bawahnya secara otomatis
    // Kita buat duplikat logika sendFirebaseNotification atau require func.
    // Karena cron_notifier mengeksekusi curl dan exit, kita salin saja fungsi murninya ke sini agar aman.
    
    $result = sendFirebaseNotificationDirect("Aktivitas Baru!", $message, $credentialFile);
    
    echo json_encode(["status" => "success", "firebase_response" => $result]);
} else {
    http_response_code(405);
    echo "Method not allowed";
}

function sendFirebaseNotificationDirect($title, $body, $credFile) {
    if (!file_exists($credFile)) {
        return "Kredensial Firebase tidak ditemukan.";
    }
    
    $jsonKey = json_decode(file_get_contents($credFile), true);
    $projectId = $jsonKey['project_id'] ?? '';
    if (!$projectId) return "Project ID tidak valid";
    
    $token = getOauth2TokenDirect($jsonKey);
    if (!$token) {
        return "Gagal mendapatkan token Oauth2.";
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

function base64url_encode_direct($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function getOauth2TokenDirect($jsonKey) {
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
    
    $base64UrlHeader = base64url_encode_direct($header);
    $base64UrlClaimSet = base64url_encode_direct($claimSet);
    $signatureInput = $base64UrlHeader . '.' . $base64UrlClaimSet;
    
    $signature = '';
    openssl_sign($signatureInput, $signature, $jsonKey['private_key'], 'SHA256');
    $base64UrlSignature = base64url_encode_direct($signature);
    
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
