<?php
/**
 * API Monitor
 * Gathers data from multiple sources:
 * 1. maktabah (MySQL)
 * 2. quizb upgrade (MySQL)
 * 3. wirid analytics (JSON)
 * 4. tahajjud data_tracker (JSON)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// DB Credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'quic1934_zenhkm');
define('DB_PASS', '03Maret1990');

$response = [
    'status' => 'success',
    'timestamp' => date('Y-m-d H:i:s'),
    'data' => [
        'maktabah' => null,
        'quizb' => null,
        'wirid' => null,
        'tahajjud' => null
    ],
    'errors' => []
];

// Helper to connect to DB
function getDbConnection($dbName) {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . $dbName . ";charset=utf8mb4";
    try {
        return new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        return null;
    }
}

// 1. Maktabah DB
$maktabahDb = getDbConnection('quic1934_maktabah');
if ($maktabahDb) {
    try {
        $searchCount = $maktabahDb->query("SELECT COUNT(*) FROM search_logs")->fetchColumn();
        $downloadCount = $maktabahDb->query("SELECT COUNT(*) FROM download_logs")->fetchColumn();
        
        $response['data']['maktabah'] = [
            'search_count' => $searchCount,
            'download_count' => $downloadCount
        ];
    } catch (Exception $e) {
        $response['errors'][] = "Maktabah query failed: " . $e->getMessage();
    }
} else {
    $response['errors'][] = "Could not connect to quic1934_maktabah";
}

// 2. QuizB Upgrade DB
$quizbDb = getDbConnection('quic1934_upgrade');
if ($quizbDb) {
    try {
        $attemptCount = $quizbDb->query("SELECT COUNT(*) FROM attemps")->fetchColumn();
        
        $response['data']['quizb'] = [
            'attempt_count' => $attemptCount
        ];
    } catch (Exception $e) {
        $response['errors'][] = "QuizB query failed: " . $e->getMessage();
    }
} else {
    $response['errors'][] = "Could not connect to quic1934_upgrade";
}

// 3. Wirid Analytics (JSON via HTTP)
function fetchJsonHttp($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

$wiridJson = fetchJsonHttp('https://wirid.quizb.my.id/analytics.json');
if ($wiridJson) {
    $decoded = json_decode($wiridJson, true);
    if ($decoded !== null) {
        $response['data']['wirid'] = [
            'total_events' => count($decoded)
        ];
    } else {
        $response['errors'][] = "Failed to parse Wirid JSON";
    }
} else {
    $response['errors'][] = "Failed to fetch Wirid JSON";
}

// 4. Tahajjud Data Tracker (Local JSON file)
$tahajjudJsonPath = __DIR__ . '/../data_tracker.json';
if (file_exists($tahajjudJsonPath)) {
    $tahajjudJson = file_get_contents($tahajjudJsonPath);
    $decoded = json_decode($tahajjudJson, true);
    if ($decoded !== null) {
        $response['data']['tahajjud'] = $decoded;
    } else {
        $response['errors'][] = "Failed to parse Tahajjud JSON";
    }
} else {
    $response['errors'][] = "Tahajjud data_tracker.json not found at " . $tahajjudJsonPath;
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
