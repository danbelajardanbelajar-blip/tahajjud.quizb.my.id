<?php
namespace app\Controllers;

use app\Core\Controller;

class HomeController extends Controller {
    public function index() {
        $this->render('home');
    }

    public function dashboard() {
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            $this->redirect('/login');
        }
        $this->render('dashboard');
    }

    public function tracker() {
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            $this->redirect('/login');
        }
        
        $dataFile = ROOT_DIR . '/data_tracker.json';
        $visits = [];
        if (file_exists($dataFile)) {
            $json = file_get_contents($dataFile);
            $visits = json_decode($json, true) ?: [];
        }
        
        $this->render('tracker', ['visits' => $visits]);
    }
}
