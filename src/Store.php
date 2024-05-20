<?php

declare(strict_types=1);

namespace Oct8pus;

use HttpSoft\Message\Response;
use HttpSoft\Message\Stream;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Store
{
    public function run() : ResponseInterface
    {
        /*
        $file = __DIR__ . '/../.env.php';

        if (!file_exists($file)) {
            echo <<<'TXT'
            Please create env.php based on env.php.example

            TXT;
        }

        $config = Config::load($file);
        */

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                return $this->show();

            case 'POST':
                return $this->purchase();
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

    private function purchase() : ResponseInterface
    {
        /*
        $router->add('orders create <intent> <amount> <currency>', static function (array $args) use ($sandbox, $handler, $auth) : void {
            $orders = new Orders($sandbox, $handler, $auth);
            $response = $orders->create(Intent::fromLowerCase($args['intent']), $args['currency'], (float) $args['amount']);

            echo "go to https://www.sandbox.paypal.com/checkoutnow?token={$response['id']} to approve the payment and finally capture the payment\n\n";

            dump($response);
        });

        $router->add('orders get <id>', static function (array $args) use ($sandbox, $handler, $auth) : void {
            $orders = new Orders($sandbox, $handler, $auth);
            dump($orders->get($args['id']));
        });

        $router->add('orders authorize <id>', static function (array $args) use ($sandbox, $handler, $auth) : void {
            $orders = new Orders($sandbox, $handler, $auth);
            dump($orders->authorize($args['id']));
        });

        $router->add('orders capture <id>', static function (array $args) use ($sandbox, $handler, $auth) : void {
            $orders = new Orders($sandbox, $handler, $auth);
            dump($orders->capture($args['id']));
        });
        */

        $stream = new Stream();
        $stream->write(json_encode([
            'result' => 'OK',
        ], JSON_PRETTY_PRINT));

        return new Response(200, ['Content-Type' => 'application/json'], $stream);
    }
}
