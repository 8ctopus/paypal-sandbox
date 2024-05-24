<?php

declare(strict_types=1);

use HttpSoft\Emitter\SapiEmitter;
use HttpSoft\Message\Response;
use HttpSoft\Message\ServerRequestFactory;
use HttpSoft\Message\Stream;
use HttpSoft\ServerRequest\ServerRequestCreator;
use Oct8pus\NanoRouter\NanoRouter;
use Oct8pus\NanoRouter\Route;
use Oct8pus\NanoRouter\RouteException;
use Oct8pus\NanoRouter\RouteType;
use Oct8pus\Store\Routes;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

require_once __DIR__ . '/../vendor/autoload.php';

$serverRequest = ServerRequestCreator::createFromGlobals($_SERVER, $_FILES, $_COOKIE, $_GET, $_POST);

$router = new NanoRouter(Response::class, ServerRequestFactory::class, true, true);

$router->addRoute(new Route(RouteType::Exact, 'GET', '/', static function (ServerRequestInterface $request) : ResponseInterface {
    return (new Routes($request))
        ->showStore();
}));

$router->addRoute(new Route(RouteType::Exact, 'GET', '/order-success/', static function (ServerRequestInterface $request) : ResponseInterface {
    return (new Routes($request))
        ->capturePayment();
}));

$router->addRoute(new Route(RouteType::Exact, 'GET', '/order-cancel/', static function (ServerRequestInterface $request) : ResponseInterface {
    return (new Routes($request))
        ->showCancel();
}));

// from here only API routes
$router->addRoute(new Route(RouteType::Exact, 'POST', '/orders/', static function (ServerRequestInterface $request) : ResponseInterface {
    return (new Routes($request))
        ->createOrder();
}));

$router->addRoute(new Route(RouteType::Exact, 'GET', '/products/', static function (ServerRequestInterface $request) : ResponseInterface {
    return (new Routes($request))
        ->listProducts();
}));

$router->addRoute(new Route(RouteType::Exact, 'POST', '/products/', static function (ServerRequestInterface $request) : ResponseInterface {
    return (new Routes($request))
        ->createProduct();
}));

$router->addRoute(new Route(RouteType::Exact, 'GET', '/plans/', static function (ServerRequestInterface $request) : ResponseInterface {
    return (new Routes($request))
        ->listPlans();
}));

$router->addRoute(new Route(RouteType::Exact, 'POST', '/plans/', static function (ServerRequestInterface $request) : ResponseInterface {
    return (new Routes($request))
        ->createPlan();
}));

$router->addRoute(new Route(RouteType::Exact, 'POST', '/subscriptions/', static function (ServerRequestInterface $request) : ResponseInterface {
    return (new Routes($request))
        ->createSubscription();
}));

$router->addRoute(new Route(RouteType::Exact, 'GET', '/hooks/', static function (ServerRequestInterface $request) : ResponseInterface {
    return (new Routes($request))
        ->listHooks();
}));

$router->addRoute(new Route(RouteType::Exact, 'POST', '/hooks/', static function (ServerRequestInterface $request) : ResponseInterface {
    return (new Routes($request))
        ->createHook();
}));

$router->addRoute(new Route(RouteType::Exact, 'DELETE', '/hooks/', static function (ServerRequestInterface $request) : ResponseInterface {
    return (new Routes($request))
        ->deleteHook();
}));

$router->addRoute(new Route(RouteType::Exact, 'POST', '/hooks/simulate/', static function (ServerRequestInterface $request) : ResponseInterface {
    return (new Routes($request))
        ->simulateHook();
}));

$router->addRoute(new Route(RouteType::Exact, 'POST', '/hooks/notify/', static function (ServerRequestInterface $request) : ResponseInterface {
    return (new Routes($request))
        ->hookNotification();
}));

try {
    $response = $router->resolve($serverRequest);
} catch (Exception $exception) {
    $stream = new Stream();
    $stream->write($exception->getMessage());

    if ($exception instanceof RouteException) {
        $response = new Response($exception->getCode(), [], $stream);
    } else {
        $response = new Response(500, [], $stream);
    }
}

(new SapiEmitter())
    ->emit($response);
