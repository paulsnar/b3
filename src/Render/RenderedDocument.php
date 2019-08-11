<?php declare(strict_types=1);
namespace PN\B3\Render;

/**
 * RenderedDocument represents a full HTML document that has been rendered from
 * a RenderableInterface implementation.
 */
class RenderedDocument
{
  public $origin, $content;

  public function __construct(
    RenderableInterface $origin,
    string $content
  ) {
    $this->origin = $origin;
    $this->content = $content;
  }
}
