<?php declare(strict_types=1);
namespace PN\B3\Render;

interface SimpleRendererInterface
{
  public function render(string $input): string;
}
