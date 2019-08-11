<?php declare(strict_types=1);
namespace PN\B3\Core;
use PN\B3\Render\{RenderableInterface, RenderedDocument, RendererInterface};

/**
 * A BlogPost is a piece of content which has an associated date.
 *
 * BlogPosts and other content types are collected within the Index and various
 * Feeds, if they should be visible in that context.
 *
 * Currently, only a single flat taxonomy of blog posts is implemented, fit for
 * a blog, as is implied in the name.
 */
class BlogPost extends Page
  implements
    RenderableInterface,
    Address\UrlAddressableInterface
{
  use ContentPiece\CanLoadFromFile;

  const DATE_FORMAT = 'Y-m-d H:i:s P';

  public $date;

  public function __construct(string $content, array $metadata = [ ])
  {
    parent::__construct($content, $metadata);

    if (array_key_exists('date', $this->metadata)) {
      $date = $this->metadata['date'];
      if (is_string($date)) {
        $date = \DateTime::createFromFormat(static::DATE_FORMAT, $date);
      }
      $this->date = $date;
    }
  }

  public function renderSelf(RendererInterface $renderer): RenderedDocument
  {
    $this->generateHtmlRepresentation($renderer);

    $page = $renderer->renderTemplate('post.html', [
      'meta' => $this->metadata,
      'date' => $this->date,
      'content' => $this->contentHtml,
    ]);
    return new RenderedDocument($this, $page);
  }

  public function isVisible(): bool
  {
    if (array_key_exists('state', $this->metadata)) {
      return $this->metadata['state'] !== 'draft';
    }
    return true;
  }

  public function getUrl(): string
  {
    return '/' . $this->date->format('Y/m') . '/' . $this->getSlug();
  }

  public function getSummary(): string
  {
    if (array_key_exists('summary', $this->metadata)) {
      return $this->metadata['summary'];
    }

    return '';
  }
}
