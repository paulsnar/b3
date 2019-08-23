<?php declare(strict_types=1);
namespace PN\B3\Util;
use PN\B3\Events\EventTarget;

trait Singleton
{
  public static function getInstance()
  {
    static $instance;
    if ($instance === null) {
      $instance = new static();
      if ($instance instanceof EventTarget) {
        $instance->dispatchEvent('b3.singletonboot');
      }
      return $instance;
    }
    return $instance;
  }
}
