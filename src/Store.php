<?php

declare(strict_types=1);

namespace Oct8pus\Store;

use HttpSoft\Message\RequestFactory;
use HttpSoft\Message\Response;
use HttpSoft\Message\Stream;
use HttpSoft\Message\StreamFactory;
use Nimbly\Shuttle\Shuttle;
use Noodlehaus\Config;
use Oct8pus\PayPal\HttpHandler;
use Oct8pus\PayPal\OAuthCache;
use Oct8pus\PayPal\Orders;
use Oct8pus\PayPal\Orders\Intent;
use Oct8pus\PayPal\PayPalException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Store
{
    private readonly ServerRequestInterface $request;
    private readonly Config $config;
    private readonly HttpHandler $handler;
    private readonly bool $sandbox;
    private readonly Orders $orders;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;

        $file = __DIR__ . '/../.env.php';

        if (!file_exists($file)) {
            echo <<<'TXT'
            Please create env.php based on env.php.example

            TXT;
        }

        $this->config = Config::load($file);

        $this->handler = new HttpHandler(
            // PSR-18 http client
            new Shuttle(),
            // PSR-17 request factory
            new RequestFactory(),
            // PSR-7 stream
            new StreamFactory()
        );

        $this->sandbox = $this->config->get('paypal.rest.sandbox');

        $auth = new OAuthCache($this->sandbox, $this->handler, $this->config->get('paypal.rest.id'), $this->config->get('paypal.rest.secret'), __DIR__ . '/../.cache.json');

        $this->orders = new Orders($this->sandbox, $this->handler, $auth);
    }

    public function run() : ResponseInterface
    {
        switch ($this->request->getMethod()) {
            case 'GET':
                return $this->show();

            case 'POST':
                return $this->createOrder();
        }
    }

    private function show() : ResponseInterface
    {
        $loader = new FilesystemLoader(__DIR__ . '/../views');

        $environment = new Environment($loader, [
            'auto_reload' => true,
            'cache' => sys_get_temp_dir(),
            'debug' => false,
            'strict_variables' => true,
        ]);

        $output = $environment->render('Store.twig', []);

        $stream = new Stream();
        $stream->write($output);

        return new Response(200, ['Content-Type' => 'text/html'], $stream);
    }

    private function createOrder() : ResponseInterface
    {
        /*
        $router->add('orders get <id>', static function (array $args) use ($sandbox, $this->handler, $auth) : void {
            $orders = new Orders($sandbox, $this->handler, $auth);
            dump($orders->get($args['id']));
        });
        */

        $json = json_decode((string) $this->request->getBody(), true);

        try {
            $response = $this->orders->create(Intent::Capture, $json['currency'], $json['amount']);
        } catch (PayPalException $exception) {
            throw $exception;
        }

        $redirect = "https://www.sandbox.paypal.com/checkoutnow?token={$response['id']}";

        $stream = new Stream();
        $stream->write(json_encode([
            'result' => 'OK',
            'order' => $response['id'],
            'redirect' => $redirect,
        ], JSON_PRETTY_PRINT));

        return new Response(200, ['Content-Type' => 'application/json'], $stream);
    }

    private function capturePayment() : void
    {
        $json = json_decode((string) $this->request->getBody(), true);

        try {
            $response = $this->orders->capture($json['id']);
        } catch (PayPalException $exception) {
            throw $exception;
        }
    }
}
