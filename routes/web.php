<?php

/**
 * @var $router \Laravel\Lumen\Routing\Router
 */

$router->group(['prefix' => 'api'], function () use ( $router ) {
    $router->post('/login', 'LoginController@login');
    $router->post('/logout', 'LoginController@logout');

    $router->get('/admins', 'AdminController@list');
    $router->post('/admins/create', 'AdminController@create');
    $router->post('/admins/update', 'AdminController@update');
    $router->post('/admins/delete', 'AdminController@delete');
    $router->post('/admins/edit', 'AdminController@edit');
    $router->post('/admins/upload', 'AdminController@upload');
    $router->get('/admins/info', 'AdminController@info');
    $router->get('/admins/roles', 'AdminController@roles');

    $router->get('/roles', 'RoleController@list');
    $router->post('/roles/create', 'RoleController@create');
    $router->post('/roles/update', 'RoleController@update');
    $router->post('/roles/delete', 'RoleController@delete');
    $router->post('/roles/edit', 'RoleController@edit');
    $router->get('/roles/permissions', 'RoleController@permissions');

    $router->get('/permissions', 'PermissionController@list');
    $router->post('/permissions/create', 'PermissionController@create');
    $router->post('/permissions/update', 'PermissionController@update');
    $router->post('/permissions/delete', 'PermissionController@delete');
    $router->post('/permissions/edit', 'PermissionController@edit');
    $router->get('/permissions/trees', 'PermissionController@trees');
});

$router->get('/{any:.*}', function ()
{
    return view('app');
});
