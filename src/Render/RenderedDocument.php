<?php declare(strict_types=1);
namespace PN\B3\Render;
use PN\B3\Core\Document;

/**
 * RenderedDocument represents a document that has an attached rendered
 * representation.
 *
 * Note that the rendered content is not wrapped within a template but only
 * represents the conversion of the actual content into an HTML partial.
 *
 * Instances of this object should be created only by the Renderer.
 */
class RenderedDocument extends Document
{
  public $renderedContent;

  public function __construct(
    string $content,
    array $metadata,
    string $renderedContent
  ) {
    parent::__construct($content, $metadata);
    $this->renderedContent = $renderedContent;
  }
}
