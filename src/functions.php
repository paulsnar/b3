<?php declare(strict_types=1);
namespace PN\B3;

function ensure_config() {
  static $required = false;
  if ( ! $required) {
    require path_join(dirname(__DIR__), 'config.php');
    $required = true;
  }
}

function set_error_handler() {
  \set_error_handler(function ($severity, $msg, $file, $line) {
    throw new \ErrorException($msg, 0, $severity, $file, $line);
  });
}

function path_join(...$parts) {
  return implode(DIRECTORY_SEPARATOR, $parts);
}

function print_warning(string $format, ...$args) {
  $message = sprintf($format, ...$args);
  $frames = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
  $location = $frames[0];
  $caller = $frames[1];

  $warning = new Runtime\Warnings\Warning($message,
    $caller, $location['file'], $location['line']);
  Runtime\Warnings\WarningManager::getGlobalInstance()->printWarning($warning);
}

function str_starts_with(string $haystack, string $needle): bool {
  return substr($haystack, 0, strlen($needle)) === $needle;
}

function str_ends_with(string $haystack, string $needle): bool {
  $needleLength = strlen($needle);
  return substr($haystack, -1 * $needleLength, $needleLength) === $needle;
}

function url_absolute(string $url): string {
  ensure_config();
  $base = rtrim(URL_BASE, '/');
  $url = ltrim($url, '/');
  return "{$base}/{$url}";
}
