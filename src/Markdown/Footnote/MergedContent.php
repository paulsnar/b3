<?php declare(strict_types=1);
namespace PN\B3\Markdown\Footnote;
use League\CommonMark\{ContextInterface, Cursor};
use League\CommonMark\Block\Element\AbstractBlock;

class MergedContent extends AbstractBlock
{
  protected $contentItems;

  public function __construct(array $items)
  {
    $this->contentItems = $items;
  }

  public function getContentItems(): array
  {
    return $this->contentItems;
  }

  public function canContain(AbstractBlock $block): bool
  {
    return $block instanceof Content;
  }

  public function isCode(): bool
  {
    return false;
  }

  public function matchesNextLine(Cursor $cursor): bool
  {
    return false;
  }

  public function finalize(ContextInterface $context, int $endLineNumber)
  {
  }
}
