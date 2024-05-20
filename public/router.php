<?php

declare(strict_types=1);

use HttpSoft\Emitter\SapiEmitter;
use Oct8pus\Store;

require_once __DIR__ . '/../vendor/autoload.php';

$response = (new Store())
    ->run();

(new SapiEmitter())
    ->emit($response);

