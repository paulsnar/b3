<?php declare(strict_types=1);
namespace PN\B3\Events;

class EventTarget
{
  protected $eventListeners = [ ];

  public function __construct()
  {
  }

  public function addEventListener(
    string $eventName,
    callable $listener,
    array $options = [ ]
  ): EventHandler {
    if ( ! ($listener instanceof \Closure)) {
      $listener = \Closure::fromCallable($listener);
    }

    $handler = new EventHandler($listener, $options);
    $handler->eventName = $eventName;

    if ( ! array_key_exists($eventName, $this->eventListeners)) {
      $this->eventListeners[$eventName] = [$handler];
    } else {
      $this->eventListeners[$eventName][] = $handler;
    }

    return $handler;
  }

  public function removeEventListener(EventHandler $handler)
  {
    $name = $handler->eventName;
    if ( ! array_key_exists($eventName, $this->eventListeners)) {
      return;
    }

    $index = array_search($handler, $this->eventHandlers[$name], true);
    if ($index === false) {
      return;
    }

    array_splice($this->eventHandlers[$name], $index, 1);
    if ($this->eventHandlers[$name] === [ ]) {
      unset($this->eventHandlers[$name]);
    }
  }

  public function dispatchEvent(string $name, ...$args)
  {
    $handlers = $this->eventListeners[$name] ?? null;
    if ($handlers === null) {
      return;
    }

    usort($handlers, function ($a, $b) {
      return $a->priority <=> $b->priority;
    });

    foreach ($handlers as $handler) {
      $handler->invoke($args);
    }
  }
}
