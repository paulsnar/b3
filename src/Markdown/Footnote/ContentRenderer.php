<?php declare(strict_types=1);
namespace PN\Blog\Markdown\Footnote;
use League\CommonMark\{ElementRendererInterface, HtmlElement};
use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Renderer\BlockRendererInterface;

class ContentRenderer implements BlockRendererInterface
{
  public function render(
    AbstractBlock $block,
    ElementRendererInterface $renderer,
    bool $inTightList = false
  ) {
    if ( ! ($block instanceof Content || $block instanceof MergedContent)) {
      throw new \RuntimeException(static::class . " cannot render " .
        get_class($block));
    }

    $renderContent = function ($content) use ($renderer) {
      return new HtmlElement('p', ['class' => 'sidenote'], [
        new HtmlElement('span', ['class' => 'sidenote-index'],
          '[' . $content->getData('index') . '] '),
        $renderer->renderInlines($content->children()),
      ]);
    };

    if ($block instanceof Content) {
      $children = $renderContent($block);
    } else if ($block instanceof MergedContent) {
      $children = array_map($renderContent, $block->getContentItems());
    }

    return new HtmlElement(
      'aside', ['class' => 'text-block__sidenote'], $children);
  }
}
