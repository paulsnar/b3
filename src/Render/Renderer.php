<?php declare(strict_types=1);
namespace PN\Blog\Render;
use PN\Blog\Core\Document;
use PN\Blog\Markdown\RendererInterface as MarkdownRendererInterface;
use PN\Blog\Template\RendererInterface as TemplateRendererInterface;

class Renderer
{
  protected $markdownRenderer, $templateRenderer;

  public function __construct(
    MarkdownRendererInterface $markdownRenderer,
    TemplateRendererInterface $templateRenderer
  ) {
    $this->markdownRenderer = $markdownRenderer;
    $this->templateRenderer = $templateRenderer;
  }

  public function renderDocument(Document $doc): RenderedDocument
  {
    try {
      $renderedContent = $this->markdownRenderer->render($doc->content);
    } catch (\Throwable $err) {
      throw new RenderException(
        $err->getMessage(), $doc->metadata['file_path'], $err);
    }

    return new RenderedDocument(
      $doc->content, $doc->metadata, $renderedContent);
  }

  public function renderPage(Document $doc): Page
  {
    if ( ! ($doc instanceof RenderedDocument)) {
      $doc = $this->renderDocument($doc);
    }

    try {
      $pageContent = $this->templateRenderer->render('post.html', [
        'document' => $doc,
      ]);
    } catch (\Throwable $err) {
      throw new RenderException(
        $err->getMessage(), $doc->metadata['file_path'], $err);
    }

    return new Page($doc, $pageContent);
  }
}