<?php declare(strict_types=1);
namespace PN\B3\Core;

class Site
{
  public $title, $url, $author;

  public static function fromGlobals()
  {
    static $site;
    if ($site !== null) {
      return $site;
    }

    $site = new Site();
    $site->title = constant('PN\\B3\\SITE_TITLE');
    $site->url = constant('PN\\B3\\SITE_BASE_URL');
    $site->author = Author::fromGlobals();

    return $site;
  }

  public function toArray()
  {
    return [
      'title' => $this->title,
      'url' => $this->url,
      'author' => $this->author->toArray(),
    ];
  }

  public function generateUrl(string $path): string
  {
    $base = rtrim($this->url, '/');
    $path = ltrim($path, '/');
    return "{$base}/{$path}";
  }
}
