<?php
session_start();

define('ROOT_DIR', dirname(__DIR__));
define('APP_DIR', ROOT_DIR . '/app');

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Simple Autoloader
spl_autoload_register(function ($class) {
    $file = ROOT_DIR . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Run Mini Firewall to block malicious bots before tracking
\app\Core\Firewall::check();

// Initialize Router
$router = new \app\Core\Router();

// Load routes
require_once ROOT_DIR . '/routes/web.php';

// Log the visit
\app\Core\Tracker::logVisit();

// Parse URL
$uri = $_SERVER['REQUEST_URI'];
// Remove /public from URI if exists (in case accessed directly or via local server)
$uri = preg_replace('/^\/public\//', '/', $uri);
if ($uri == '/public') $uri = '/';

// Strip query string
if (($pos = strpos($uri, '?')) !== false) {
    $uri = substr($uri, 0, $pos);
}
// Strip tahajjud.quizb.my.id subfolder if testing locally
$basepath = '/tahajjud.quizb.my.id';
if (strpos($uri, $basepath) === 0) {
    $uri = substr($uri, strlen($basepath));
    if ($uri == '') $uri = '/';
}

$method = $_SERVER['REQUEST_METHOD'];

// Dispatch
$router->dispatch($uri, $method);
