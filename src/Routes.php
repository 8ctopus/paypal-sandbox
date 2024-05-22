<?php

declare(strict_types=1);

namespace Oct8pus\Store;

use Exception;
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
use Oct8pus\PayPal\Plans;
use Oct8pus\PayPal\Plans\BillingCycle;
use Oct8pus\PayPal\Plans\BillingCycles;
use Oct8pus\PayPal\Plans\Frequency;
use Oct8pus\PayPal\Plans\IntervalUnit;
use Oct8pus\PayPal\Plans\PaymentPreferences;
use Oct8pus\PayPal\Plans\PricingScheme;
use Oct8pus\PayPal\Plans\SetupFeeFailure;
use Oct8pus\PayPal\Plans\Taxes;
use Oct8pus\PayPal\Plans\TenureType;
use Oct8pus\PayPal\Products;
use Oct8pus\PayPal\Status;
use Oct8pus\PayPal\Subscriptions;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Routes
{
    private readonly ServerRequestInterface $request;
    private readonly Config $config;
    private readonly HttpHandler $handler;
    private readonly OAuthCache $auth;
    private readonly bool $sandbox;
    private readonly Orders $orders;
    private readonly Environment $environment;

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

        $this->auth = new OAuthCache($this->sandbox, $this->handler, $this->config->get('paypal.rest.id'), $this->config->get('paypal.rest.secret'), __DIR__ . '/../.cache.json');

        $this->orders = new Orders($this->sandbox, $this->handler, $this->auth);

        $loader = new FilesystemLoader(__DIR__ . '/../views');

        $this->environment = new Environment($loader, [
            'auto_reload' => true,
            'cache' => sys_get_temp_dir(),
            'debug' => false,
            'strict_variables' => true,
        ]);
    }

    public function showStore() : ResponseInterface
    {
        $output = $this->environment->render('Store.twig', [
            'createOrderUrl' => '/create-order/',
            'createProductUrl' => '/create-product/',
            'createPlanUrl' => '/create-plan/',
            'createSubscriptionUrl' => '/create-subscription/',
        ]);

        $stream = new Stream();
        $stream->write($output);

        return new Response(200, ['Content-Type' => 'text/html'], $stream);
    }

    public function createOrder() : ResponseInterface
    {
        /*
        $orders = new Orders($sandbox, $this->handler, $auth);
        dump($orders->get($args['id']));
        */

        $json = json_decode((string) $this->request->getBody(), true);

        try {
            $response = $this->orders->create(Intent::Capture, $json['currency'], (int) $json['amount'], 'http://localhost/success/', 'http://localhost/cancel/');
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

    public function capturePayment() : ResponseInterface
    {
        $params = $this->request->getQueryParams();

        try {
            $response = $this->orders->capture($params['token']);

            $output = $this->environment->render('Success.twig', []);

            $stream = new Stream();
            $stream->write($output);

            return new Response(200, ['Content-Type' => 'text/html'], $stream);
        } catch (PayPalException $exception) {
            throw $exception;
        }
    }

    public function showCancel() : ResponseInterface
    {
        $output = $this->environment->render('Cancel.twig', []);

        $stream = new Stream();
        $stream->write($output);

        return new Response(200, ['Content-Type' => 'text/html'], $stream);
    }

    public function createProduct() : ResponseInterface
    {
        $products = new Products($this->sandbox, $this->handler, $this->auth);

        $response = $products->create([
            'name' => 'Test Product',
            'description' => 'Test Product Description',
            'type' => 'Digital Goods', // Physical Goods, Digital Goods, Service
            'category' => 'Software', // Software
            'home_url' => 'http://localhost/',
            'image_url' => 'http://localhost/image.jpg',
        ]);

        $stream = new Stream();
        $stream->write(json_encode($response, JSON_PRETTY_PRINT));

        return new Response(200, ['content-type' => 'application/json'], $stream);
    }

    public function createPlan() : ResponseInterface
    {
        throw new Exception();

        $plans = new Plans($this->sandbox, $this->handler, $this->auth);

        /*
        $billingCycles = (new BillingCycles())
            ->add(new BillingCycle(TenureType::Trial, new Frequency(IntervalUnit::Month, 1), 2, new PricingScheme(3, 'USD')))
            ->add(new BillingCycle(TenureType::Trial, new Frequency(IntervalUnit::Month, 1), 3, new PricingScheme(6, 'USD')))
            ->add(new BillingCycle(TenureType::Regular, new Frequency(IntervalUnit::Month, 1), 12, new PricingScheme(10, 'USD')));

        $paymentPreferences = new PaymentPreferences(true, 10, SetupFeeFailure::Continue, 3);
        $taxes = new Taxes(0.10, false);
        */

        $billingCycles = (new BillingCycles())
            ->add(new BillingCycle(TenureType::Regular, new Frequency(IntervalUnit::Month, 1), 0, new PricingScheme(4.99, 'USD')));

        $paymentPreferences = new PaymentPreferences(true, 0, SetupFeeFailure::Continue, 1);
        $taxes = new Taxes(0, false);

        $response = $plans->create(
            'product_id',
            'plan name',
            'plan description',
            Status::Active,
            $billingCycles,
            $paymentPreferences,
            $taxes
        );

        $stream = new Stream();
        $stream->write(json_encode($response, JSON_PRETTY_PRINT));

        return new Response(200, ['content-type' => 'application/json'], $stream);
    }

    public function createSubscription() : ResponseInterface
    {
        $subscriptions = new Subscriptions($this->sandbox, $this->handler, $this->auth);

        $response = $subscriptions->create($planId, $successUrl, $cancelUrl);

        foreach ($response['links'] as $link) {
            if ($link['rel'] === 'approve') {
                echo "redirect user to {$link['href']} to approve the subscription\n\n";
                break;
            }
        }

        $stream = new Stream();
        $stream->write(json_encode($response, JSON_PRETTY_PRINT));

        return new Response(200, ['content-type' => 'application/json'], $stream);
    }
}