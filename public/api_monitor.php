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
        $maktabahSearchLogs = $maktabahDb->query("SELECT COUNT(*) FROM search_logs")->fetchColumn();
        $maktabahDownloadLogs = $maktabahDb->query("SELECT COUNT(*) FROM download_logs")->fetchColumn();
        $maktabahAskLogs = $maktabahDb->query("SELECT COUNT(*) FROM ask_logs")->fetchColumn();
        
        $maktabahTodaySearch = $maktabahDb->query("SELECT COUNT(*) FROM search_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
        $maktabahTodayDownload = $maktabahDb->query("SELECT COUNT(*) FROM download_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
        $maktabahTodayAsk = $maktabahDb->query("SELECT COUNT(*) FROM ask_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
        $todayMaktabah = (int)$maktabahTodaySearch + (int)$maktabahTodayDownload + (int)$maktabahTodayAsk;
        
        $recentMaktabah = $maktabahDb->query("
            (SELECT CONCAT('Search: ', query) AS activity, created_at FROM search_logs)
            UNION ALL
            (SELECT CONCAT('Download: ', book_title) AS activity, created_at FROM download_logs)
            UNION ALL
            (SELECT CONCAT('Tanya AI: ', question) AS activity, created_at FROM ask_logs)
            ORDER BY created_at DESC LIMIT 4
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        $recentMaktabahFmt = [];
        foreach ($recentMaktabah as $r) {
            $recentMaktabahFmt[] = ['title' => mb_substr($r['activity'], 0, 80), 'subtitle' => $r['created_at']];
        }

        $response['data']['maktabah'] = [
            'search_count' => $maktabahSearchLogs,
            'download_count' => $maktabahDownloadLogs,
            'ask_count' => $maktabahAskLogs,
            'today_activity' => $todayMaktabah,
            'recent_records' => $recentMaktabahFmt
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
        $attemptCount = $quizbDb->query("SELECT COUNT(*) FROM attempts")->fetchColumn();
        $todayQuizB = $quizbDb->query("SELECT COUNT(*) FROM attempts WHERE DATE(completed_at) = CURDATE()")->fetchColumn();
        
        $recentQuizB = $quizbDb->query("
            SELECT a.score, a.completed_at, q.title as quiz_title, u.name as user_name
            FROM attempts a 
            LEFT JOIN quizzes q ON a.quiz_id = q.id 
            LEFT JOIN users u ON a.user_id = u.id
            ORDER BY a.id DESC LIMIT 4
        ")->fetchAll(PDO::FETCH_ASSOC);
        $recentQuizBFmt = [];
        foreach ($recentQuizB as $r) {
            $qTitle = $r['quiz_title'] ?? 'QuizB';
            $uName = $r['user_name'] ?? 'Anonim';
            $recentQuizBFmt[] = ['title' => "{$qTitle} oleh {$uName} - Skor: " . $r['score'], 'subtitle' => $r['completed_at']];
        }

        $response['data']['quizb'] = [
            'attempt_count' => $attemptCount,
            'today_activity' => (int)$todayQuizB,
            'recent_records' => $recentQuizBFmt
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
    $wiridDecoded = json_decode($wiridJson, true);
    if ($wiridDecoded !== null) {
        $todayStr = date('Y-m-d');
        $todayWirid = 0;
        foreach ($wiridDecoded as $r) {
            if (strpos($r['timestamp'] ?? '', $todayStr) === 0) {
                $todayWirid++;
            }
        }
        
        $recentWirid = array_slice($wiridDecoded, -4);
        $recentWiridFmt = [];
        foreach (array_reverse($recentWirid) as $r) {
            $title = $r['item_title'] ?? 'Unknown';
            $time = $r['timestamp'] ?? '';
            $recentWiridFmt[] = ['title' => $title, 'subtitle' => $time];
        }

        $response['data']['wirid'] = [
            'total_events' => count($wiridDecoded),
            'today_activity' => $todayWirid,
            'recent_records' => $recentWiridFmt
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
        $todayStr = date('Y-m-d');
        $todayTahajjud = 0;
        foreach ($decoded as $r) {
            if (strpos($r['timestamp'] ?? '', $todayStr) === 0) {
                $todayTahajjud++;
            }
        }
        
        $recentTahajjud = array_slice($decoded, -4);
        $recentTahajjudFmt = [];
        foreach (array_reverse($recentTahajjud) as $r) {
            $uri = $r['uri'] ?? 'Unknown';
            
            $friendlyName = 'Beranda';
            if ($uri !== '/' && $uri !== '' && $uri !== 'Unknown') {
                $cleanUri = trim($uri, '/');
                $friendlyName = ucwords(str_replace(['-', '_', '/'], ' ', $cleanUri));
            } elseif ($uri === 'Unknown') {
                $friendlyName = 'Unknown';
            }
            
            $title = "Halaman: " . $friendlyName;
            $time = $r['timestamp'] ?? '';
            $recentTahajjudFmt[] = ['title' => $title, 'subtitle' => $time];
        }

        $response['data']['tahajjud'] = [
            'total_visits' => count($decoded),
            'today_activity' => $todayTahajjud,
            'recent_records' => $recentTahajjudFmt
        ];
    } else {
        $response['errors'][] = "Failed to parse Tahajjud JSON";
    }
} else {
    $response['errors'][] = "Tahajjud data_tracker.json not found at " . $tahajjudJsonPath;
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
