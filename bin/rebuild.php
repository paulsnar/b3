<?php declare(strict_types=1);
namespace PN\B3;
use PN\B3\Core\Index;
use PN\B3\Core\Feed\JsonFeed;

require 'vendor/autoload.php';

set_error_handler();

(function () {
  $renderer = new Render\Renderer(
    new Markdown\Renderer(),
    new Template\Renderer(dirname(__DIR__) . '/templates'));

  $dataManager = new Data\DataManager();
  $filesystemLoader = new Data\FilesystemLoader();

  $pages = $filesystemLoader->loadPages();
  $posts = $filesystemLoader->loadPosts();

  foreach (array_merge($pages, $posts) as $piece) {
    $path = $dataManager->getPathFor($piece);
    $targetDir = substr($path, 0, strrpos($path, DIRECTORY_SEPARATOR) + 1);
    if ( ! is_dir($targetDir)) {
      mkdir($targetDir, 0755, true);
    }
    $rendered = $renderer->render($piece);
    file_put_contents($path, $rendered->content);
  }

  $index = new Index($posts);
  $indexPage = $renderer->render($index);
  file_put_contents($dataManager->getPathFor($indexPage), $indexPage->content);

  $feed = new JsonFeed($posts);
  file_put_contents($dataManager->getPathFor($feed), $feed->renderToText());

  $count = count($pages) + count($posts) + 2;
  echo "[b3] Rendered {$count} files\n";
})();
