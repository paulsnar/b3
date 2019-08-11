<?php declare(strict_types=1);
namespace PN\B3\Data;
use PN\B3\Core;
use PN\B3\Render\RenderedDocument;
use function PN\B3\{path_join, print_warning, str_starts_with};

class DataManager
{
  public $root;
  // public $metadataStorage;

  protected $sourceRoot, $outputRoot;

  public static function getSiteRoot(): string
  {
    if (array_key_exists('B3_SITE_ROOT', $_ENV)) {
      $root = $_ENV['B3_SITE_ROOT'];
    } else {
      $root = dirname(dirname(__DIR__));
    }
    return $root;
  }

  public function __construct()
  {
    $this->root = static::getSiteRoot();

    // $this->metadataStorage = new MetadataStorage($this->root);

    $this->sourceRoot = path_join($this->root, 'site');
    $this->outputRoot = path_join($this->root, 'public');
  }

  public function getPathFor($item): string
  {
    if ($item instanceof RenderedDocument) {
      return $this->getPathFor($item->origin);
    } else if ($item instanceof Core\Index) {
      return path_join($this->outputRoot, 'index.html');
    } else if ($item instanceof Core\Address\UrlAddressableInterface) {
      $url = $item->getUrl();
      $pieces = explode('/', $url);
      $lastPiece = $pieces[count($pieces) - 1];
      if (strpos($lastPiece, '.') === false) {
        $lastPiece .= '.html';
        $pieces[count($pieces) - 1] = $lastPiece;
      }
      return path_join($this->outputRoot,
        implode(DIRECTORY_SEPARATOR, $pieces));
    }
  }
}
