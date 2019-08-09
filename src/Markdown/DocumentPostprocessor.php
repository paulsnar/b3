<?php declare(strict_types=1);
namespace PN\Blog\Markdown;
use League\CommonMark\Node\Node;
use League\CommonMark\Block\Element\{Heading, Paragraph};
use League\CommonMark\Event\DocumentParsedEvent;
use PN\Blog\Markdown\Footnote\{Content, MergedContent};

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
        $parent = $node->parent();
        $siblings = $parent->children();

        $previous = null;
        $next = null;

        while ($siblings[0] !== $node) {
          $previous = array_shift($siblings);
        }

        $items = [ ];
        while ($siblings[0] !== null && $siblings[0] instanceof Content) {
          $items[] = array_shift($siblings);
        }

        if (count($siblings) > 0) {
          $next = array_shift($siblings);
        }

        if (count($items) > 0) {
          foreach ($items as $item) {
            $item->detach();
          }
          $merged = new MergedContent($items);

          if ($previous !== null) {
            $previous->insertAfter($merged);
          } else if ($next !== null) {
            $next->insertBefore($merged);
          }

          $walker->resumeAt($merged, false);
        }
      } else if ($node instanceof Heading) {
        self::setClass($node, 'heading');
      } else if (
          $node instanceof Paragraph &&
          ! ($node instanceof Footnote\Content)) {
        self::setClass($node, 'text text-block__text');
      }
    }
  }
}
