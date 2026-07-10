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
        
        // Calculate statistics
        $totalVisits = count($visits);
        $uniqueIPs = array_unique(array_column($visits, 'ip'));
        $totalUniqueIPs = count($uniqueIPs);
        
        // Today's visits
        $today = date('Y-m-d');
        $todayVisits = array_filter($visits, function($v) use ($today) {
            return strpos($v['timestamp'], $today) === 0;
        });
        $todayCount = count($todayVisits);
        
        // Visits by date (last 7 days)
        $visitsByDate = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $visitsByDate[$date] = 0;
        }
        foreach ($visits as $v) {
            $date = substr($v['timestamp'], 0, 10);
            if (isset($visitsByDate[$date])) {
                $visitsByDate[$date]++;
            }
        }
        
        // Top URIs
        $uriCounts = [];
        foreach ($visits as $v) {
            $uri = $v['uri'] ?? '/';
            $uriCounts[$uri] = ($uriCounts[$uri] ?? 0) + 1;
        }
        arsort($uriCounts);
        $topURIs = array_slice($uriCounts, 0, 10, true);
        
        $this->render('tracker', [
            'visits' => $visits,
            'totalVisits' => $totalVisits,
            'totalUniqueIPs' => $totalUniqueIPs,
            'todayCount' => $todayCount,
            'visitsByDate' => $visitsByDate,
            'topURIs' => $topURIs
        ]);
    }
}
