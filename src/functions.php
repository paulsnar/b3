<?php declare(strict_types=1);
namespace PN\B3;

function set_error_handler() {
  \set_error_handler(function ($severity, $msg, $file, $line) {
    throw new \ErrorException($msg, 0, $severity, $file, $line);
  });
}
