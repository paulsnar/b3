<?php declare(strict_types=1);
namespace PN\B3\Template;

interface RendererInterface
{
  public function render(string $name, array $context = [ ]): string;
}
