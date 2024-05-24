# PayPal sandbox

A PayPal REST api sandbox to experiment with payments and subscriptions

![php sandbox screenshot](https://github.com/8ctopus/paypal-sandbox/raw/master/screenshot.png)

## requirements

- php 8.x
- composer
- ngrok

## demo

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

- finally head to `http://localhost` in your browser

The demo automatically lists all products, plans and existing webhooks. You can create new products, plans and webhooks. You can also create an order (payment) or subscription.
