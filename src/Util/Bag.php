<?php declare(strict_types=1);
namespace PN\B3\Util;
use function PN\B3\array_pluck;

/**
 * A Bag is a simple wrapper for an array, which provides hooks for overriding
 * array access methods. (They also look nicer if using a Bag instead of an
 * array somewhere.)
 */
class Bag implements \ArrayAccess, \Iterator
{
  protected $items = [ ], $iterator;

  public function __construct(array $initial = [ ])
  {
    $this->items = $initial;
  }

  public function get(string $key, $default = null)
  {
    return $this->items[$key] ?? $default;
  }

  public function set(string $key, $value)
  {
    return $this->items[$key] = $value;
  }

  public function has(string $key): bool
  {
    return array_key_exists($key, $this->items);
  }

  public function unset(string $key)
  {
    if (array_key_exists($key, $this->items)) {
      unset($this->items[$key]);
    }
  }

  public function toArray(): array
  {
    return $this->items;
  }

  // Some utility functions too.

  public function isEmpty(): bool
  {
    return $this->items === [ ];
  }

  public function count(): int
  {
    return count($this->items);
  }

  public function pluck(...$keys): array
  {
    return array_pluck($this->items, ...$keys);
  }

  /* interface \ArrayAccess */
  public function offsetExists($key) { return $this->has($key); }
  public function offsetGet($key) { return $this->get($key); }
  public function offsetSet($key, $value) { return $this->set($key, $value); }
  public function offsetUnset($key) { return $this->unset($key); }

  /* interface \Iterator */
  public function rewind()
  {
    $iterate = function (array $items) {
      foreach ($items as $key => $value) {
        yield $key => $value;
      }
    };
    $this->iterator = $iterate($this->items);
    return $this->iterator->rewind();
  }

  public function key() { return $this->iterator->key(); }
  public function current() { return $this->iterator->current(); }
  public function next() { return $this->iterator->next(); }
  public function valid()
  {
    return $this->iterator !== null && $this->iterator->valid();
  }
}
