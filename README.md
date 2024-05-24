# PayPal sandbox

A PayPal REST api sandbox to experiment with payments and subscriptions

![php sandbox screenshot](https://github.com/8ctopus/paypal-sandbox/raw/master/screenshot.png)

Contributions welcome!

## features

- create orders (one-time payments)
- create subscriptions
- list and create products
- list and create plans
- list, create and delete webhooks
- process webhook notifications received from PayPal

## requirements

- php 8.x
- composer
- ngrok

## demo

- git clone the project

- install composer dependencies

```sh
composer install
```

- copy `.env.example` to `.env` and fill in your PayPal REST api credentials. If you don't have credentials yet, follow the guide:

    https://developer.paypal.com/api/rest/

- You will also need both a sandbox user account and sandbox business account. You get those from:

    https://developer.paypal.com/dashboard/accounts

- start the website

```sh
php -S localhost:80 public/router.php
```

- start ngrok to receive PayPal notifications to your development environment

```sh
ngrok http 80
```

- head to `http://localhost` in your browser

The demo when started automatically lists all existing products, plans and webhooks.

If you never used the PayPal REST api before, you will need to create a product, a plan and a webhook before you can use subscriptions.

## debugging

To debug the javacript code, the browser needs to be started with the remote debugging option. For example, on Windows:

```sh
"C:\Program Files\Google\Chrome\Application\chrome.exe" --remote-debugging-port=9222
```

Then in Visual Studio Code, use `js attach to browser`.
