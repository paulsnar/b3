<?php declare(strict_types=1);
namespace PN\B3\Core\Address;

interface UrlAddressableInterface
{
  public function getUrl(): string;
}
