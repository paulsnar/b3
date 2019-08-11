<?php declare(strict_types=1);
namespace PN\B3\Render;
use PN\B3\Core\{Document, Page};
use PN\B3\Markdown\RendererInterface as MarkdownRendererInterface;
use PN\B3\Template\{RendererInterface as TemplateRendererInterface,
  TemplateNotFoundException};

class Renderer implements RendererInterface
{
  protected $markdownRenderer, $templateRenderer;

  public function __construct(
    MarkdownRendererInterface $markdownRenderer,
    TemplateRendererInterface $templateRenderer
  ) {
    $this->markdownRenderer = $markdownRenderer;
    $this->templateRenderer = $templateRenderer;
  }

  public function renderMarkdown(string $markdown): string
  {
    return $this->markdownRenderer->render($markdown);
  }

  public function renderTemplate(string $name, array $context = [ ]): string
  {
    return $this->templateRenderer->render($name, $context);
  }

  public function render(RenderableInterface $item): RenderedDocument
  {
    return $item->renderSelf($this);
  }
}
