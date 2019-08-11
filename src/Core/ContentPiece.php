<?php declare(strict_types=1);
namespace PN\B3\Core;

/**
 * A ContentPiece represents a standalone piece of content, which has some
 * associated metadata and some content.
 *
 * Initially the content is in an unrendered form; when it's rendered, that
 * representation is stored within contentHtml, which is null until then.
 *
 * The metadata are typically strings; further transformations (such as parsing
 * dates) happens in subclasses.
 */
class ContentPiece
{
  /** @var string */
  public $content;

  /** @var string|null */
  public $contentHtml;

  /** @var string[] */
  public $metadata = [ ];

  public function __construct(string $content, array $metadata = [ ])
  {
    $this->content = $content;
    $this->metadata = $metadata;
  }
}
