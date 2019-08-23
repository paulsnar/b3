<?php declare(strict_types=1);
namespace PN\B3\Http;
use PN\B3\Util\Bag;
use function PN\B3\str_starts_with;

class HeaderBag extends Bag
{
  public function __construct(array $initial = [ ])
  {
    $headers = [ ];
    foreach ($initial as $name => $value) {
      $headers[strtolower($name)] = $value;
    }
    parent::__construct($headers);
  }

  public static function fromGlobals(): self
  {
    $headers = [ ];
    foreach ($_SERVER as $key => $value) {
      if (str_starts_with($key, 'HTTP_')) {
        $key = substr($key, 5);
        $key = str_replace('_', '-', $key);
        $headers[$key] = $value;
      }
    }
    return new static($headers);
  }

  public function has(string $key): bool
  {
    return parent::has(strtolower($key));
  }

  public function get(string $key, $default = null)
  {
    return parent::get(strtolower($key), $default);
  }

  public function set(string $key, $value)
  {
    return parent::set(strtolower($key), $value);
  }

  public function unset(string $key)
  {
    return parent::unset(strtolower($key));
  }
}
