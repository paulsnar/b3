<?php declare(strict_types=1);
namespace PN\B3\Extension;
use PN\B3\{App, EventBus, Extension};
use PN\B3\Util\Singleton;

class Registry
{
  use Singleton;

  protected $extensions = [ ];

  public function __construct()
  {
    App::getInstance()->addEventListener('b3.extensionsloaded', function () {
      $this->bootExtensions();
    });
  }

  public function register(array $attributes, Extension $instance)
  {
    $attributes['system'] = App::getInstance()->getInternalState(
      'b3.is-importing-system-extension');
    $attributes['instance'] = $instance;
    $attributes['enabled'] = true; // TODO: make this toggleable?

    $id = $attributes['id'] ?? null;
    if ($id === null) {
      throw new \RuntimeException('Cannot create extension without an id');
    }

    $this->extensions[$id] = $attributes;
  }

  public function bootExtensions()
  {
    foreach ($this->extensions as $extension) {
      if ($extension['enabled'] || $extension['system']) {
        $e = $extension['instance'];
        $e->isEnabled = true;
        $e->dispatchEvent('b3-ext.boot');
      }
    }
  }

  public function getExtensions(): array
  {
    return $this->extensions;
  }
}
