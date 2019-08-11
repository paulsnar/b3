<?php declare(strict_types=1);
namespace PN\B3\Markdown\Footnote;
use League\CommonMark\{ElementRendererInterface, HtmlElement};
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;

class InlineRenderer implements InlineRendererInterface
{
  public function render(
    AbstractInline $inline,
    ElementRendererInterface $renderer
  ) {
    if ( ! ($inline instanceof Inline)) {
      throw new \RuntimeException(static::class . " cannot render " .
        get_class($inline));
    }

    $text = '[' . $inline->getData('index') . ']';
    return new HtmlElement('span', ['class' => 'sidenote-ref'], $text);
  }
}
