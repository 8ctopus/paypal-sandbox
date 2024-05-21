# PayPal sandbox

Playing with the PayPal REST api. Still a work in progress...

## demo

Install composer dependencies

```sh
composer install
```

Copy `.env.example` to `.env` and fill in your PayPal REST api credentials. If you don't have credentials yet, follow the guide:

    https://developer.paypal.com/api/rest/

Start the website

```sh
php -S localhost:80 public/router.php
```

Finally head to `http://localhost` in your browser
