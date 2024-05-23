# PayPal sandbox

A sandbox to demonstrate PayPal payments and subscriptions with the REST API.

## requirements

- php 8.x installed
- composer installed
- ngrok installed

## demo

Install composer dependencies

```sh
composer install
```

- Copy `.env.example` to `.env` and fill in your PayPal REST api credentials. If you don't have credentials yet, follow the guide:

    https://developer.paypal.com/api/rest/

- Start the website

```sh
php -S localhost:80 public/router.php
```

- Start ngrok to receive PayPal notifications to your development environment

```sh
ngrok http 80
```

- Finally head to `http://localhost` in your browser

The demo automatically lists all products, plans and existing webhooks.
