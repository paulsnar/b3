<?php declare(strict_types=1);
namespace PN\B3\Http;
use PN\B3\App;

class Session
{
  protected static $isStarted = false;
  protected static function ensureStarted()
  {
    if ( ! static::$isStarted) {
      session_start([
        'name' => 'b3-session',
      ]);
      static::$isStarted = true;

      App::getInstance()->addEventListener('b3.shutdown', function () {
        static::clearFlash();
      });
    }
  }

  public static function has(string $key): bool
  {
    static::ensureStarted();
    return array_key_exists($key, $_SESSION);
  }

  public static function get(string $key)
  {
    static::ensureStarted();
    return $_SESSION[$key] ?? null;
  }

  public static function set(string $key, $value)
  {
    static::ensureStarted();
    return $_SESSION[$key] = $value;
  }

  public static function unset(string $key)
  {
    static::ensureStarted();
    unset($_SESSION[$key]);
  }

  protected static $consumeFlash = [ ];

  public static function hasFlash(string $key)
  {
    $key = 'session.flash.' . $key;
    return static::has($key);
  }

  public static function getFlash(string $key)
  {
    $key = 'session.flash.' . $key;
    try {
      return static::get($key);
    } finally {
      if (array_key_exists($key, $_SESSION)) {
        static::$consumeFlash[$key] = true;
      }
    }
  }

  public static function setFlash(string $key, $value)
  {
    $key = 'session.flash.' . $key;
    return static::set($key, $value);
  }

  public static function unsetFlash(string $key)
  {
    $key = 'session.flash.' . $key;
    return static::unset($key);
  }

  public static function clearFlash()
  {
    foreach (static::$consumeFlash as $key => $true) {
      unset($_SESSION[$key]);
    }
  }
}
