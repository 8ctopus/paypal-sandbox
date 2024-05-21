<?php

declare(strict_types=1);

use HttpSoft\Emitter\SapiEmitter;
use HttpSoft\Message\Response;
use HttpSoft\Message\ServerRequestFactory;
use HttpSoft\ServerRequest\ServerRequestCreator;
use Oct8pus\NanoRouter\NanoRouter;
use Oct8pus\NanoRouter\Route;
use Oct8pus\NanoRouter\RouteType;
use Oct8pus\Store\Store;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

require_once __DIR__ . '/../vendor/autoload.php';

$serverRequest = ServerRequestCreator::createFromGlobals($_SERVER, $_FILES, $_COOKIE, $_GET, $_POST);

$router = new NanoRouter(Response::class, ServerRequestFactory::class, true, true);

$router->addRoute(new Route(RouteType::Exact, 'GET', '/', function (ServerRequestInterface $request) : ResponseInterface {
    return (new Store($request))
        ->showStore();
}));

$router->addRoute(new Route(RouteType::Exact, 'GET', '/success/', function (ServerRequestInterface $request) : ResponseInterface {
    return (new Store($request))
        ->capturePayment();
}));

$router->addRoute(new Route(RouteType::Exact, 'GET', '/cancel/', function (ServerRequestInterface $request) : ResponseInterface {
    return (new Store($request))
        ->showCancel();
}));

$router->addRoute(new Route(RouteType::Exact, 'POST', '/create-order/', function (ServerRequestInterface $request) : ResponseInterface {
    return (new Store($request))
        ->createOrder();
}));

$router->resolve($serverRequest);

(new SapiEmitter())
    ->emit($response);
