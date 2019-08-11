<?php declare(strict_types=1);
namespace PN\B3\Data;
use PN\B3\Core\{BlogPost, Page};
use function PN\B3\path_join;

/**
 * FilesystemLoader loads BlogPosts from a predefined location.
 */
class FilesystemLoader
{
  public $root;

  public function __construct()
  {
    $this->root = path_join(DataManager::getSiteRoot(), 'site');
  }

  public function loadPages(): array
  {
    $pages = [ ];

    $pageNames = scandir($this->root);
    foreach ($pageNames as $pageName) {
      $fullPath = path_join($this->root, $pageName);
      if ($pageName[0] === '.' || is_dir($fullPath)) {
        continue;
      }

      $pages[] = Page::fromFile($fullPath);
    }

    return $pages;
  }

  public function loadPosts(): array
  {
    $posts = [ ];

    $postsRoot = path_join($this->root, 'posts');
    $postNames = scandir($postsRoot);
    foreach ($postNames as $postName) {
      if ($postName[0] === '.') {
        continue;
      }

      $postName = path_join($postsRoot, $postName);
      $posts[] = BlogPost::fromFile($postName);
    }

    return $posts;
  }
}

