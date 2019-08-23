<?php declare(strict_types=1);
namespace PN\B3\Markdown;
use League\CommonMark\Node\Node;
use League\CommonMark\Block\Element\{Heading, Paragraph};
use League\CommonMark\Event\DocumentParsedEvent;
use PN\B3\Markdown\Footnote\{Content, ContentParagraphContainer,
  MergedContent};

class DocumentPostprocessor
{
  public function onDocumentParsed(DocumentParsedEvent $event)
  {
    $doc = $event->getDocument();

    $walker = $doc->walker();
    while ($event = $walker->next()) {
      if ( ! $event->isEntering()) {
        continue;
      }

      $node = $event->getNode();

      if ($node instanceof Content) {
        $this->amalgamateFootnotes($walker, $event, $node);
      }
    }
  }

  protected function amalgamateFootnotes($walker, $event, $node)
  {
    $previous = $node->previous();

    $adjacentFootnotes = [$node];
    $sibling = $node->next();
    while ($sibling instanceof Content) {
      $adjacentFootnotes[] = $sibling;
      $sibling = $sibling->next();
    }

    if ($adjacentFootnotes !== [ ]) {
      $mergedNode = new MergedContent($adjacentFootnotes);
      $node->replaceWith($mergedNode);
      $node = $mergedNode;

      array_shift($adjacentFootnotes);
      foreach ($adjacentFootnotes as $footnote) {
        $footnote->detach();
      }
    }

    if ($previous !== null && $previous instanceof Paragraph) {
      $node->detach();
      $node = new ContentParagraphContainer($previous, $node);
      $previous->replaceWith($node);
    }

    $walker->resumeAt($node, false);
  }
}
