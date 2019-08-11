<?php declare(strict_types=1);
namespace PN\B3\Markdown;

interface RendererInterface
{
  public function render(string $markdown): string;
}
