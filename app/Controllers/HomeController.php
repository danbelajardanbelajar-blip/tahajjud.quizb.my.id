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
}
