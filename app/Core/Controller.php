<?php
namespace app\Core;

class Controller {
    protected function render($view, $data = []) {
        extract($data);
        // Assuming all views are wrapped in a layout for SPA, 
        // actually for SPA we might want to return HTML fragments if requested via Fetch,
        // or full page if requested normally.
        
        $isFetch = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $isFetch = $isFetch || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
        // Wait, for this simple SPA, we will always serve layout.php for GET requests 
        // to main routes, and let JS fetch the actual content if needed.
        // Actually, the user asked for: "Gunakan index.php sebagai front controller, Router, Controller, Model, View, API JSON, Fetch API, dan History API agar navigasi serta tambah/edit/hapus berjalan tanpa reload halaman."
        
        // Let's send the full layout if it's a direct browser hit, or just the JSON/fragment if requested via API.
        
        // We will output HTML here.
        ob_start();
        require APP_DIR . "/Views/{$view}.php";
        $content = ob_get_clean();
        
        if (isset($_GET['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'fetch')) {
            echo $content; // Return partial view
        } else {
            require APP_DIR . "/Views/layout.php";
        }
    }

    protected function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function redirect($url) {
        header("Location: $url");
        exit;
    }
}
