<?php declare(strict_types=1);
namespace PN\B3\Markdown\Footnote;
use League\CommonMark\{ElementRendererInterface, HtmlElement};
use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Renderer\BlockRendererInterface;

class ContentParagraphRenderer implements BlockRendererInterface
{
  public function render(
    AbstractBlock $block,
    ElementRendererInterface $renderer,
    bool $inTightList = false
  ) {
    if ( ! ($block instanceof ContentParagraphContainer)) {
      throw new \RuntimeException(static::class . ' cannot render ' .
        get_class($block));
    }

    $paragraph = $block->getParagraph();
    $paragraph = $renderer->renderBlock($paragraph, $inTightList);

    $content = $block->getContent();
    $content = $renderer->renderBlock($content, $inTightList);

    return new HtmlElement('div', ['class' => 'post-sidenote-container'],
      [$paragraph, $content]);
  }
}
