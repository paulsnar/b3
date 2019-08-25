<?php declare(strict_types=1);
namespace PN\B3;

function path_join(...$parts): string {
  return implode(DIRECTORY_SEPARATOR, $parts);
}

function url_join(...$parts): string {
  if ($parts === [ ]) {
    return '';
  }

  $url = array_shift($parts);
  foreach ($parts as $part) {
    $url = rtrim($url, '/');
    $part = ltrim($part, '/');
    $url .= '/' . $part;
  }
  return $url;
}

function str_starts_with(string $haystack, string $needle): bool {
  return substr($haystack, 0, strlen($needle)) === $needle;
}

function str_ends_with(string $haystack, string $needle): bool {
  return substr($haystack, -1 * strlen($needle)) === $needle;
}

/** @var string|string[] $needles */
function str_maskpos(string $haystack, $needles, int $offset = 0): ?int {
  $resultOffset = $offset;
  if ($offset >= strlen($haystack) || $offset < -strlen($haystack)) {
    return null;
  } else if ($offset < 0) {
    $resultOffset = strlen($haystack) + $offset;
    $haystack = substr($haystack, $offset);
  } else if ($offset > 0) {
    $haystack = substr($haystack, $offset);
  }

  if (is_string($needles)) {
    $needles = str_split($needles);
  }

  $min = INF;
  foreach ($needles as $needle) {
    if ($needle === '') {
      continue;
    }
    $pos = strpos($haystack, $needle);
    if ($pos !== false) {
      $min = $pos;
      $haystack = substr($haystack, 0, $pos);
    }
  }
  if ($min === INF) {
    return null;
  }
  return $resultOffset + $min;
}

function array_pluck(array $array, ...$keys): array {
  $result = [ ];
  foreach ($keys as $key) {
    if (array_key_exists($key, $array)) {
      $result[$key] = $array[$key];
    }
  }
  return $result;
}

function array_without(array $array, ...$keys): array {
  foreach ($keys as $key) {
    if (array_key_exists($key, $array)) {
      unset($array[$key]);
    }
  }
  return $array;
}

// Like the ?? operator, but doesn't pick RHS on falsy values that are actually
// present in the array.
function array_index(array $array, $index, $default = null) {
  if (array_key_exists($index, $array)) {
    return $array[$index];
  }
  return $default;
}

// This deliberately exists as an independent function so that it can be called
// from within objects and return only the publicly accessible properties
// instead of scope-accessible ones.
function obj_get_properties($object): array {
  return get_object_vars($object);
}

// Return an array with the selected accessible properties of an object.
// Essentially array_pluck, but doesn't require casting the object to an array
// which would be a hack anyway.
// Works with __get.
function obj_pluck($object, ...$properties): array {
  $result = [ ];
  foreach ($properties as $property) {
    try {
      $result[$property] = $object->$property;
    } catch (\Throwable $err) { /* noop */ }
  }
  return $result;
}

function iter_collect(\Iterator $c): array {
  $array = [ ];
  foreach ($c as $key => $value) {
    $array[$key] = $value;
  }
  return $array;
}

// Same as \scandir, but returns absolute paths and filters out dotfiles.
function dir_scan(string $dir) {
  $items = scandir($dir);
  $items = array_filter($items, function ($item) {
    return $item[0] !== '.';
  });
  $items = array_map(function ($item) use ($dir) {
    return path_join($dir, $item);
  }, $items);
  return $items;
}

function dir_iterate_files(string $dir, bool $recurse = true) {
  foreach (dir_scan($dir) as $item) {
    if (is_file($item)) {
      yield $item;
    } else if (is_directory($item) && $recurse) {
      yield from dir_iterate_files($item, $recurse);
    }
  }
}

function dir_list_files(string $dir, bool $recursive = true) {
  return iter_collect(dir_iterate_files($dir, $recursive));
}

// Replace file at $target, if it exists, with a new one that contains $content.
// Strives to guarantee atomicity, but it's not entirely foolproof. Avoid using
// concurrently.
function file_write(string $target, string $content) {
  $nameStart = strrpos($target, DIRECTORY_SEPARATOR);
  if ($nameStart !== false) {
    $directory = substr($target, 0, $nameStart);
    if ( ! is_dir($directory)) {
      mkdir($directory, 0777, true);
    }
    $name = substr($target, $nameStart + 1);
  } else {
    $directory = '.';
    $name = $target;
  }


  $nameTemp = '.' . $name . '.' . uniqid();
  file_put_contents(path_join($directory, $nameTemp), $content);
  rename(path_join($directory, $nameTemp), path_join($directory, $name));
}

function debug_print(string $format, ...$args) {
  $bt = debug_backtrace(2);
  $self = $bt[0];
  $bt = $bt[1];
  $call = ($bt['class'] ?? false) ?
    "{$bt['class']}{$bt['type']}{$bt['function']}" : $bt['function'];
  $call = sprintf('%s:%d (%s)',
    $self['file'] ?? '<none>', $self['line'] ?? 0, $call);

  if ($args !== [ ]) {
    $format = vsprintf($format, $args);
  }
  if (substr($format, -1) !== "\n") {
    $format .= "\n";
  }

  file_put_contents('php://stderr', "{$call}: {$format}");
}

function debug_dump(...$items) {
  $bt = debug_backtrace(2);
  $self = $bt[0];
  $bt = $bt[1];
  $call = ($bt['class'] ?? false) ?
    "{$bt['class']}{$bt['type']}{$bt['function']}" : $bt['function'];
  $call = sprintf('%s:%d (%s)',
    $self['file'] ?? '<none>', $self['line'] ?? 0, $call);

  $message = '';
  foreach ($items as $item) {
    $message .= $call . ': ' . print_r($item, true) . "\n";
  }
  file_put_contents('php://stderr', $message);
}
