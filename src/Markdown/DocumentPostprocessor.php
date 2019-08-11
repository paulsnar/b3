<?php declare(strict_types=1);
namespace PN\B3\Markdown;
use League\CommonMark\Node\Node;
use League\CommonMark\Block\Element\{Heading, Paragraph};
use League\CommonMark\Event\DocumentParsedEvent;
use PN\B3\Markdown\Footnote\{Content, ContentParagraphContainer,
  MergedContent};

class DocumentPostprocessor
{
  protected static function setClass(Node $node, string $setClass)
  {
    $attributes = $node->getData('attributes', [ ]);
    $class = $attributes['class'] ?? '';
    if ($class !== '') {
      $class .= ' ';
    }
    $class .= $setClass;
    $attributes['class'] = $class;
    $node->data['attributes'] = $attributes;
  }

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
      } else if ($node instanceof Heading) {
        self::setClass($node, 'heading');
      } else if (
          $node instanceof Paragraph &&
          ! ($node instanceof Footnote\Content)) {
        self::setClass($node, 'text post-paragraph');
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

    if (count($adjacentFootnotes) > 0) {
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
