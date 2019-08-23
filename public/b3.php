<?php declare(strict_types=1);
namespace PN\B3;

require dirname(__DIR__) . DIRECTORY_SEPARATOR .
  'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

App::getInstance()->run();
