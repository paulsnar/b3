<?php declare(strict_types=1);
namespace PN\B3\Render;

/**
 * RenderableInterface is implemented by objects that can render themselves
 * using a Renderer.
 */
interface RenderableInterface
{
  /**
   * Render this object.
   */
  public function renderSelf(RendererInterface $renderer): RenderedDocument;
}
