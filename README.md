# PayPal sandbox

Playing with the PayPal REST api. Still a work in progress...

## demo

Copy `.env.example` to `.env` and fill in your PayPal REST api credentials. If you don't have credentials yet, go to and follow the guide:

    https://developer.paypal.com/api/rest/

Install composer dependencies and start the local server

```sh
composer install
php -S localhost:80 public/router.php
```

Finally head to `http://localhost` in your browser.
