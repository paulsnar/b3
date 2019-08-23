<?php declare(strict_types=1);
namespace PN\B3\Render;
use PN\B3\Markdown;
use PN\B3\Util\Singleton;

class Context
{
  use Singleton;

  public static function contentRenderer(string $type): SimpleRendererInterface
  {
    return static::getInstance()->getContentRenderer($type);
  }

  public $contentRenderers = [ ];

  public function __construct()
  {
    $this->contentRenderers['markdown'] = new Markdown\Renderer();
  }

  public function getContentRenderer(string $type): SimpleRendererInterface
  {
    if ( ! array_key_exists($type, $this->contentRenderers)) {
      throw new \RuntimeException(
        "No content renderer present for content type: {$type}");
    }
    return $this->contentRenderers[$type];
  }
}
