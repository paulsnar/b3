<?php declare(strict_types=1);
namespace PN\Blog\Experiment;
use PN\Blog\Core\Document;
use PN\Blog\Markdown\Renderer as MarkdownRenderer;
use PN\Blog\Template\Renderer as TemplateRenderer;
use PN\Blog\Render\Renderer;

require 'vendor/autoload.php';

set_error_handler(function ($severity, $msg, $file, $line) {
  throw new \ErrorException($msg, 0, $severity, $file, $line);
});

function w(string $format, ...$args) {
  if (count($args) === 0) {
    echo "{$format}" . PHP_EOL;
  } else {
    $msg = sprintf($format, ...$args);
    echo "{$msg}" . PHP_EOL;
  }
}

exit((function (): int {
  $args = $_SERVER['argv'];
  if (count($args) < 2) {
    w('Usage: %s in.md', $args[0]);
    return 1;
  }

  $path = $args[1];
  if ($path[0] !== '/') {
    $path = getcwd() . '/' . $path;
  }

  try {
    $post = Document::fromFile($path);
  } catch (\ErrorException $err) {
    w('Error: could not get file contents: %s', $err->getMessage());
    return 1;
  }

  $r = new Renderer(
    new MarkdownRenderer(),
    new TemplateRenderer(__DIR__ . '/templates'));
  $page = $r->renderPage($post);

  echo $page->content, PHP_EOL;

  return 0;
})());
