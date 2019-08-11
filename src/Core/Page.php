<?php declare(strict_types=1);
namespace PN\B3\Core;
use PN\B3\Render\{RenderableInterface, RenderedDocument, RendererInterface};

/**
 * A Page is a specific kind of ContentPiece -- one which is fully static and
 * does not belong to any taxonomies, and is not listed within any collections.
 */
class Page extends ContentPiece
  implements
    RenderableInterface,
    Address\UrlAddressableInterface
{
  use ContentPiece\CanLoadFromFile;

  public $title;

  public function __construct(string $content, array $metadata = [ ])
  {
    parent::__construct($content, $metadata);

    $this->title = $this->metadata['title'] ?? null;

    if (($metadata['file_extension'] ?? null) === 'html') {
      $this->contentHtml = $this->content;
    }
  }

  protected function generateHtmlRepresentation(RendererInterface $renderer)
  {
    if ($this->contentHtml !== null) {
      return;
    }

    if ($this->metadata['file_extension'] === 'md') {
      $this->contentHtml = $renderer->renderMarkdown($this->content);
    } else {
      throw new \RuntimeException('Page cannot render own content: ' .
        'unknown file extension: ' . $this->metadata['file_extension']);
    }
  }

  public function renderSelf(RendererInterface $renderer): RenderedDocument
  {
    $this->generateHtmlRepresentation($renderer);

    $page = $renderer->renderTemplate('page.html', [
      'meta' => $this->metadata,
      'content' => $this->contentHtml,
    ]);
    return new RenderedDocument($this, $page);
  }

  public function getSlug(): string
  {
    if (array_key_exists('slug', $this->metadata)) {
      return $this->metadata['slug'];
    } else if (array_key_exists('title', $this->metadata)) {
      $slug = $this->metadata['title'];
      $slug = preg_replace('/[^A-Za-z0-9]+/', '-', $slug);
      $slug = strtolower($slug);
      return $slug;
    } else if (array_key_exists('file_path', $this->metadata)) {
      $slug = $item->metadata['file_path'];

      // trim path, leaving file name
      $slug = substr($slug, strrpos($slug, DIRECTORY_SEPARATOR) + 1);

      // trim extension
      $slug = substr($slug, 0, strrpos($slug, '.'));

      // trim digit prefixes (e.g., dates)
      if (ctype_digit($slug[0])) {
        $slug = preg_replace('/^[0-9]+/', '', $slug);
      }

      $slug = preg_replace('/[^A-Za-z0-9]+/', '-', $slug);
      $slug = strtolower($slug);
      return $slug;
    } else {
      throw new \RuntimeException('Cannot determine slug for page');
    }
  }

  public function getUrl(): string
  {
    return '/' . $this->getSlug();
  }
}
