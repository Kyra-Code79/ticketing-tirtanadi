<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Public Routes
$routes->get('/', 'Pengaduan::index');
$routes->post('pengaduan/store', 'Pengaduan::store');

// Auth Routes
$routes->get('login', 'AuthController::login');
$routes->post('login/process', 'AuthController::process_login');
$routes->get('logout', 'AuthController::logout');

// Admin Routes (Protected)
$routes->group('admin', ['filter' => 'auth'], function($routes) {
    $routes->get('dashboard', 'Admin::index');
    $routes->get('laporan', 'Admin::laporan');
    $routes->get('laporan/detail/(:num)', 'Admin::detail/$1');
    $routes->post('laporan/update/(:num)', 'Admin::update_status/$1');
    
    // Polygon Management
    $routes->get('polygons', 'PolygonController::index');
    $routes->post('polygons/store', 'PolygonController::store');
    $routes->get('polygons/delete/(:num)', 'PolygonController::delete/$1');
});
