<?php

declare(strict_types=1);

/** @var App\Core\Router $router */

// Vitrine (US01, US02)
$router->get('/', 'ShopController@index');
$router->get('/api/produtos', 'ShopController@products');
$router->get('/produto/{id}', 'ShopController@show');

// Carrinho (US03, US04)
$router->get('/carrinho', 'CartController@index');
$router->post('/carrinho/adicionar', 'CartController@add', ['csrf']);
$router->post('/carrinho/atualizar', 'CartController@update', ['csrf']);
$router->post('/carrinho/remover', 'CartController@remove', ['csrf']);

// Conta (US11)
$router->get('/cadastro', 'AuthController@registerForm');
$router->post('/cadastro', 'AuthController@register', ['csrf']);
$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login', ['csrf']);
$router->post('/logout', 'AuthController@logout', ['csrf']);

// Endereços (US15)
$router->get('/enderecos', 'AddressController@index', ['auth']);
$router->post('/enderecos', 'AddressController@store', ['auth', 'csrf']);
$router->get('/enderecos/{id}/editar', 'AddressController@edit', ['auth']);
$router->post('/enderecos/{id}', 'AddressController@update', ['auth', 'csrf']);
$router->post('/enderecos/{id}/excluir', 'AddressController@destroy', ['auth', 'csrf']);

// Checkout (US04, US05, US06)
$router->get('/checkout', 'CheckoutController@index', ['auth']);
$router->get('/api/checkout/cotacao', 'CheckoutController@quote', ['auth']);
$router->get('/api/checkout/horarios', 'CheckoutController@slots', ['auth']);
$router->post('/checkout', 'CheckoutController@place', ['auth', 'csrf']);

// Pedidos do cliente (US07)
$router->get('/pedidos', 'OrderController@index', ['auth']);
$router->get('/pedidos/{id}', 'OrderController@show', ['auth']);
$router->get('/api/pedidos/{id}/status', 'OrderController@status', ['auth']);

// Área do gerente (US09, US10, US07)
$router->get('/admin', 'Admin\\OrderController@index', ['gerente']);
$router->post('/admin/pedidos/{id}/status', 'Admin\\OrderController@status', ['gerente', 'csrf']);
$router->post('/admin/pedidos/{id}/pagamento', 'Admin\\OrderController@confirmPayment', ['gerente', 'csrf']);
$router->get('/admin/pedidos/{id}', 'Admin\\OrderController@show', ['gerente']);

$router->get('/admin/produtos', 'Admin\\ProductController@index', ['gerente']);
$router->get('/admin/produtos/novo', 'Admin\\ProductController@create', ['gerente']);
$router->post('/admin/produtos', 'Admin\\ProductController@store', ['gerente', 'csrf']);
$router->get('/admin/produtos/{id}/editar', 'Admin\\ProductController@edit', ['gerente']);
$router->post('/admin/produtos/{id}', 'Admin\\ProductController@update', ['gerente', 'csrf']);
$router->post('/admin/produtos/{id}/excluir', 'Admin\\ProductController@destroy', ['gerente', 'csrf']);

$router->get('/admin/categorias', 'Admin\\CatalogController@categories', ['gerente']);
$router->post('/admin/categorias', 'Admin\\CatalogController@storeCategory', ['gerente', 'csrf']);
$router->post('/admin/categorias/{id}/excluir', 'Admin\\CatalogController@destroyCategory', ['gerente', 'csrf']);
$router->post('/admin/coberturas', 'Admin\\CatalogController@storeCovering', ['gerente', 'csrf']);
$router->post('/admin/coberturas/{id}/alternar', 'Admin\\CatalogController@toggleCovering', ['gerente', 'csrf']);

$router->get('/admin/relatorios', 'Admin\\ReportController@index', ['gerente']);
$router->get('/admin/relatorios/csv', 'Admin\\ReportController@csv', ['gerente']);

$router->get('/admin/configuracoes', 'Admin\\SettingsController@index', ['gerente']);
$router->post('/admin/configuracoes', 'Admin\\SettingsController@update', ['gerente', 'csrf']);
