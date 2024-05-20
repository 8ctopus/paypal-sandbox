<?php

declare(strict_types=1);

use Noodlehaus\Config;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once __DIR__ . '/vendor/autoload.php';

/*
$file = __DIR__ . '/.env.php';

if (!file_exists($file)) {
    echo <<<'TXT'
    Please create env.php based on env.php.example

    TXT;
}

$config = Config::load($file);
*/

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $loader = new FilesystemLoader(__DIR__ . '/views');

        $environment = new Environment($loader, [
            'auto_reload' => true,
            'cache' => sys_get_temp_dir(),
            'debug' => false,
            'strict_variables' => true,
        ]);

        echo $environment->render('Store.twig', []);
        break;

    case 'POST':
        echo json_encode([
            'result' => 'OK',
        ], JSON_PRETTY_PRINT);
        break;
}
