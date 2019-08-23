<?php declare(strict_types=1);
namespace PN\B3\Events;

class EventHandler
{
  public $eventName, $handler, $priority, $options = [ ];

  public function __construct(
    \Closure $handler,
    array $options = [ ]
  ) {
    $this->handler = $handler;
    $this->priority = $options['priority'] ?? 100;
    $this->options = $options;
  }

  public function invoke(array $args)
  {
    ($this->handler)(...$args);
  }
}
