<?php declare(strict_types=1);
namespace PN\Blog\Render;
use PN\Blog\Core\Document;

/**
 * Page contains the representation of a Document as rendered within a template.
 *
 * The content of a Page can be persisted unto storage for serving statically.
 */
class Page
{
  public $document, $content;

  public function __construct(Document $doc, string $content)
  {
    $this->document = $doc;
    $this->content = $content;
  }
}