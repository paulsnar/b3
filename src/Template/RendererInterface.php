<?php declare(strict_types=1);
namespace PN\Blog\Template;

interface RendererInterface
{
  public function render(string $name, array $context = [ ]): string;
}
