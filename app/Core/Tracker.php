<?php
namespace app\Core;

class Tracker {
    public static function logVisit() {
        $dataFile = ROOT_DIR . '/data_tracker.json';
        $visits = [];
        if (file_exists($dataFile)) {
            $json = file_get_contents($dataFile);
            $visits = json_decode($json, true) ?: [];
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Ignore tracking for /tracker, /api, or assets if requested directly
        if (strpos($uri, '/api') === 0 || strpos($uri, '/tracker') === 0 || strpos($uri, '/assets') === 0) {
            return;
        }

        $visit = [
            'ip' => $ip,
            'user_agent' => $userAgent,
            'uri' => $uri,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        array_unshift($visits, $visit); // Add to beginning
        
        // Keep only last 1000 visits to avoid massive file size
        $visits = array_slice($visits, 0, 1000);

        file_put_contents($dataFile, json_encode($visits, JSON_PRETTY_PRINT));
        
        // [REALTIME NOTIFIKASI] Tembak sinyal ke Tahajjud API secara asinkron
        $notifyUrl = 'https://tahajjud.quizb.my.id/api_notify.php';
        $postData = http_build_query([
            'secret' => 'QUIZB_NOTIFY_SECRET_99',
            'message' => 'Ada pengunjung baru di Web Tahajjud!'
        ]);
        
        $ch = curl_init($notifyUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }
}
