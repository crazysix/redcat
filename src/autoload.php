<?php

/**
 * @file
 * Primary autoloader.
 */

function code_challege_loader($className) {
  $replace = [
    '\\' => DIRECTORY_SEPARATOR,
    'CodeChallenge' => BASE_PATH . DIRECTORY_SEPARATOR . 'src',
  ];
  $file = str_replace(array_keys($replace), $replace, $className);
  require_once $file . '.php';
}

spl_autoload_register('code_challege_loader');
