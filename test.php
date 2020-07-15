#!/usr/local/opt/php@8.0/bin/php
<?php

require_once 'vendor/autoload.php';

use Octris\App;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$rh = new class() implements App\Request\RequestHandlerInterface {
    public function handle(Request $request, Response $response): Response
    {
    }
};

$collector = new App\Router\RouteCollector();
$collector->addGroup('/admin', function (App\Router\RouteCollector $r) {
    $r->addRoute([ 'GET' ], 'login', '/login', function ($req, $res) {
        print "hallo";
    }, function () {
        return true;
    });
});

$m = new App\MiddlewareDispatcher($rh);

/*$router = new App\Router($collector, [ 'cacheDisabled' => false, 'cacheFile' => __DIR__ . '/cache.json' ]);

$app = new class($router) extends App\AbstractApp {
    protected function initialize(): void
    {
        // TODO: Implement initialize() method.
    }
};

$request = Request::create(
    '/admin/login',
    'GET',
    array('name' => 'admin')
);

$app->handle($request); */
