<?php declare(strict_types=1);
namespace PN\B3\Render;

interface RendererInterface
{
  public function renderMarkdown(string $markdown): string;
  public function renderTemplate(string $name, array $context = [ ]): string;
}
