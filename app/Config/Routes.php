<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Public Routes
$routes->get('/', 'Pengaduan::index');
$routes->post('pengaduan/store', 'Pengaduan::store');
$routes->get('pengaduan/store', 'Pengaduan::index'); // Fallback for GET access
$routes->get('pengaduan/cetak/(:segment)', 'Pengaduan::cetak_pdf/$1');
$routes->get('cek-status', 'Pengaduan::cek_status');

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

    // Kantor (Offices)
    $routes->get('kantor', 'KantorController::index');
    $routes->get('kantor/create', 'KantorController::create');
    $routes->post('kantor/store', 'KantorController::store');
    $routes->get('kantor/edit/(:num)', 'KantorController::edit/$1');
    $routes->post('kantor/update/(:num)', 'KantorController::update/$1');
    $routes->get('kantor/delete/(:num)', 'KantorController::delete/$1');
    $routes->post('kantor/update/(:num)', 'KantorController::update/$1');
    $routes->get('kantor/delete/(:num)', 'KantorController::delete/$1');
    $routes->post('kantor/parse-map', 'KantorController::parse_maps_url');

    // Office Types
    $routes->get('types', 'TypeController::index');
    $routes->get('types/create', 'TypeController::create');
    $routes->post('types/store', 'TypeController::store');
    $routes->get('types/edit/(:num)', 'TypeController::edit/$1');
    $routes->post('types/update/(:num)', 'TypeController::update/$1');
    $routes->post('types/update/(:num)', 'TypeController::update/$1');
    $routes->get('types/delete/(:num)', 'TypeController::delete/$1');

    // User Management
    $routes->get('users', 'UserController::index');
    $routes->get('users/create', 'UserController::create');
    $routes->post('users/store', 'UserController::store');
    $routes->get('users/edit/(:num)', 'UserController::edit/$1');
    $routes->post('users/update/(:num)', 'UserController::update/$1');
    $routes->get('users/delete/(:num)', 'UserController::delete/$1');
    // Role Management
    $routes->get('roles', 'RoleController::index');
    $routes->get('roles/create', 'RoleController::create');
    $routes->post('roles/store', 'RoleController::store');
    $routes->get('roles/edit/(:num)', 'RoleController::edit/$1');
    $routes->post('roles/update/(:num)', 'RoleController::update/$1');
    $routes->get('roles/delete/(:num)', 'RoleController::delete/$1');
    
    // Assignment
    $routes->post('laporan/assign/(:num)', 'Admin::assign_teknisi/$1');
});

// Teknisi Routes
$routes->group('teknisi', ['filter' => 'auth'], function($routes) {
    $routes->get('dashboard', 'TeknisiController::index');
    $routes->post('update-status/(:num)', 'TeknisiController::update_status/$1');
});
