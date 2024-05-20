<?php

declare(strict_types=1);

use HttpSoft\Emitter\SapiEmitter;
use HttpSoft\ServerRequest\ServerRequestCreator;
use Oct8pus\Store\Store;

require_once __DIR__ . '/../vendor/autoload.php';

$serverRequest = ServerRequestCreator::createFromGlobals($_SERVER, $_FILES, $_COOKIE, $_GET, $_POST);

$response = (new Store($serverRequest))
    ->run();

(new SapiEmitter())
    ->emit($response);

