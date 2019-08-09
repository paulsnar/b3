<?php declare(strict_types=1);
namespace PN\Blog\Markdown;
use League\CommonMark\ConfigurableEnvironmentInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\ExtensionInterface;

class Extension implements ExtensionInterface
{
  public function register(ConfigurableEnvironmentInterface $env)
  {
    $env

      ->addInlineParser(new Footnote\InlineParser(), 500)
      ->addInlineRenderer(Footnote\Inline::class, new Footnote\InlineRenderer())

      ->addBlockParser(new Footnote\ContentParser())
      ->addBlockRenderer(Footnote\Content::class,
        $contentRenderer = new Footnote\ContentRenderer())
      ->addBlockRenderer(Footnote\MergedContent::class, $contentRenderer)

      ->addBlockRenderer(Footnote\ContentParagraphContainer::class,
        new Footnote\ContentParagraphRenderer())

      ->addEventListener(DocumentParsedEvent::class,
        [new DocumentPostprocessor(), 'onDocumentParsed']);
  }
}
