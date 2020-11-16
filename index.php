<?php

/**
 * @file
 * The PHP page that serves all functionality in the RedCat Coding Challenge.
 */

define('BASE_PATH', realpath(dirname(__FILE__)));

require_once BASE_PATH . '/src/autoload.php';

use CodeChallenge\Routing\RequestRouter;

$router = new RequestRouter();
$router->run();
