<?php declare(strict_types=1);
namespace PN\B3;
use PN\B3\Events\{EventHandler, EventTarget};

abstract class Extension extends EventTarget
{
  public $isEnabled = false;

  public function __construct(array $attributes)
  {
    parent::__construct();

    Extension\Registry::getInstance()->register($attributes, $this);
  }

  protected function addGlobalEventListener(
    string $eventName,
    callable $listener,
    array $options = [ ]
  ) {
    $listener = \Closure::fromCallable($listener);
    $install = function () use ($eventName, $listener, $options) {
      App::getInstance()->addEventListener($eventName, $listener, $options);
    };
    $this->addEventListener('b3-ext.boot', $install);
  }
}
