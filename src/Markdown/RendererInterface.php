<?php declare(strict_types=1);
namespace PN\Blog\Markdown;

interface RendererInterface
{
  public function render(string $markdown): string;
}
