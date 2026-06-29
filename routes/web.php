<?php
use app\Controllers\HomeController;
use app\Controllers\AuthController;
use app\Controllers\ApiController;

// Front-end Views (handled by HomeController)
$router->get('/', [HomeController::class, 'index']);
$router->get('/dashboard', [HomeController::class, 'dashboard']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'processLogin']);
$router->post('/logout', [AuthController::class, 'logout']);

// API Endpoints
$router->get('/api/doa', [ApiController::class, 'getAll']);
$router->get('/api/doa/{id}', [ApiController::class, 'getOne']);
$router->post('/api/doa', [ApiController::class, 'createOrUpdate']);
$router->post('/api/doa/reorder', [ApiController::class, 'reorder']);
$router->delete('/api/doa/{id}', [ApiController::class, 'delete']);
