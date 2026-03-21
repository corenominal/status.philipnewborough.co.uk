<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/status/(:segment)', 'Home::show/$1');
$routes->get('/timeline/load', 'Home::loadMoreStatuses');
$routes->get('/feed/rss', 'Feed::rss');

// Admin routes
$routes->get('/admin', 'Admin\Home::index');
$routes->get('/admin/export', 'Admin\Export::index');
$routes->get('/admin/export/(:segment)', 'Admin\Export::download/$1');

// API routes
$routes->match(['get', 'options'], '/api/test/ping', 'Api\Test::ping');

// Status CRUD
$routes->match(['post', 'options'], '/api/statuses', 'Api\Statuses::create');
$routes->match(['get', 'options'], '/api/statuses/(:num)', 'Api\Statuses::get/$1');
$routes->match(['patch', 'options'], '/api/statuses/(:num)', 'Api\Statuses::update/$1');
$routes->match(['delete', 'options'], '/api/statuses/(:num)', 'Api\Statuses::delete/$1');

// Media upload and delete
$routes->match(['post', 'options'], '/api/media', 'Api\Media::upload');
$routes->match(['delete', 'options'], '/api/media/(:num)', 'Api\Media::delete/$1');

// Drafts CRUD
$routes->match(['get', 'options'], '/api/drafts', 'Api\Drafts::index');
$routes->match(['post', 'options'], '/api/drafts', 'Api\Drafts::create');
$routes->match(['patch', 'options'], '/api/drafts/(:num)', 'Api\Drafts::update/$1');
$routes->match(['delete', 'options'], '/api/drafts/(:num)', 'Api\Drafts::delete/$1');

// Command line routes
$routes->cli('cli/test/index/(:segment)', 'CLI\Test::index/$1');
$routes->cli('cli/test/count', 'CLI\Test::count');

// Metrics route
$routes->post('/metrics/receive', 'Metrics::receive');

// Logout route
$routes->get('/logout', 'Auth::logout');

// Unauthorised route
$routes->get('/unauthorised', 'Unauthorised::index');

// Custom 404 route
$routes->set404Override('App\Controllers\Errors::show404');

// Debug routes
$routes->get('/debug', 'Debug\Home::index');
$routes->get('/debug/(:segment)', 'Debug\Rerouter::reroute/$1');
$routes->get('/debug/(:segment)/(:segment)', 'Debug\Rerouter::reroute/$1/$2');
