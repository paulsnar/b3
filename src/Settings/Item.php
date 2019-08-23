<?php declare(strict_types=1);
namespace PN\B3\Settings;

class Item
{
  public
    $key,
    $value,
    $name,
    $description;

  public function __construct(
    string $key,
    $value,
    string $name,
    string $description
  ) {
    $this->key = $key;
    $this->value = $value;
    $this->name = $name;
    $this->description = $description;
  }
}
