<?php
namespace app\Controllers;

use app\Core\Controller;

class AuthController extends Controller {
    public function showLogin() {
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
            $this->redirect('/dashboard');
        }
        $this->render('login');
    }

    public function processLogin() {
        $password = $_POST['password'] ?? '';
        
        // Hardcoded password for simple implementation
        if ($password === 'admin123') {
            $_SESSION['logged_in'] = true;
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'fetch') {
                $this->json(['success' => true]);
            }
            $this->redirect('/dashboard');
        } else {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'fetch') {
                $this->json(['success' => false, 'message' => 'Password salah'], 401);
            }
            // For non-fetch requests we should redirect back to login, but simple SPA handles via fetch
            $this->redirect('/login?error=1');
        }
    }

    public function logout() {
        session_destroy();
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'fetch') {
            $this->json(['success' => true]);
        }
        $this->redirect('/');
    }
}
