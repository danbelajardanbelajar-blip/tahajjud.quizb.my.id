<?php
namespace app\Core;

class Firewall {
    public static function check() {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Patterns of known scanners, exploits, or sensitive files we don't have
        $badPatterns = [
            '/\.sql$/i',
            '/\.bak$/i',
            '/\.env/i',
            '/\.yaml$/i',
            '/\.yml$/i',
            '/\.cfg$/i',
            '/\.ini$/i',
            '/\.aws/i',
            '/\.git/i',
            '/phpunit/i',
            '/wp-admin/i',
            '/wp-login/i',
            '/wp-content/i',
            '/eval-stdin/i',
            '/_next/i',
            '/_middleware/i',
            '/\.action/i',
            '/_rsc/i',
            '/composer\.json/i',
            '/package\.json/i',
            '/\/RSC\//i'
        ];

        foreach ($badPatterns as $pattern) {
            if (preg_match($pattern, $uri)) {
                http_response_code(403);
                echo "403 Forbidden - Access Denied by Firewall";
                exit;
            }
        }

        // Block empty User-Agents or known scanner agents like curl (unless you need API access via curl yourself)
        // For safety, we will just block common python/java bots if we want, but let's stick to URI for now as it's safer.
        if (empty($userAgent) || stripos($userAgent, 'nmap') !== false || stripos($userAgent, 'zgrab') !== false) {
            http_response_code(403);
            echo "403 Forbidden - Access Denied by Firewall";
            exit;
        }
    }
}
