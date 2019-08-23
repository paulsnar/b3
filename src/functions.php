<?php declare(strict_types=1);
namespace PN\B3;

function path_join(...$parts): string {
  return implode(DIRECTORY_SEPARATOR, $parts);
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

// This deliberately exists as an independent function so that it can be called
// from within objects and return only the publicly accessible properties
// instead of scope-accessible ones.
function obj_get_properties($object): array {
  return get_object_vars($object);
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

function debug_print(string $format, ...$args) {
  if ($args !== [ ]) {
    $format = vsprintf($format, $args);
  }
  if (substr($format, -1) !== "\n") {
    $format .= "\n";
  }

  file_put_contents('php://stderr', $format);
}

function debug_dump(...$items) {
  foreach ($items as $item) {
    file_put_contents('php://stderr', print_r($item, true));
  }
}
