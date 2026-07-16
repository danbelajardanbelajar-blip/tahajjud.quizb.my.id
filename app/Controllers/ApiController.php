<?php
namespace app\Controllers;

use app\Core\Controller;
use app\Models\DoaModel;

class ApiController extends Controller {
    private $model;

    public function __construct() {
        $this->model = new DoaModel();
    }

    private function checkAuth() {
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            $this->json(['error' => 'Unauthorized'], 401);
        }
    }

    private function verifyCsrf() {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($token) || $token !== $_SESSION['csrf_token']) {
            $this->json(['error' => 'Invalid CSRF token'], 403);
        }
    }

    public function getAll() {
        $data = $this->model->getAll();
        $this->json($data);
    }

    public function getOne($id) {
        $item = $this->model->getById($id);
        if ($item) {
            $this->json($item);
        } else {
            $this->json(['error' => 'Not found'], 404);
        }
    }

    public function createOrUpdate() {
        $this->checkAuth();
        $this->verifyCsrf();

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $id = $input['id'] ?? null;
        $arab = $input['arab'] ?? '';
        $terjemah = $input['terjemah'] ?? '';
        $repetitions = $input['repetitions'] ?? 3;

        try {
            $result = $this->model->save($arab, $terjemah, $repetitions, $id);
            $this->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function reorder() {
        $this->checkAuth();
        $this->verifyCsrf();

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $order = $input['order'] ?? [];

        try {
            $this->model->reorderData($order);
            $this->json(['success' => true]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function delete($id) {
        $this->checkAuth();
        $this->verifyCsrf();

        try {
            $this->model->delete($id);
            $this->json(['success' => true]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }
}
