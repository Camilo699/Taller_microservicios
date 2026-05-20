<?php

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Config/database.php';

$cors      = require __DIR__ . '/../app/Presentation/Middlewares/CorsMiddleware.php';
$endpoints = require __DIR__ . '/../app/Presentation/Routers/endpoints.php';

$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$cors($app);
$endpoints($app);

$app->run();
