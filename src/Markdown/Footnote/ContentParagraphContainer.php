<?php declare(strict_types=1);
namespace PN\B3\Markdown\Footnote;
use League\CommonMark\{ContextInterface, Cursor};
use League\CommonMark\Block\Element\{AbstractBlock, Paragraph};

class ContentParagraphContainer extends AbstractBlock
{
  protected $content, $paragraph;

  public function __construct(Paragraph $paragraph, $content)
  {
    $this->paragraph = $paragraph;
    $this->content = $content;
  }

  public function getParagraph(): Paragraph
  {
    return $this->paragraph;
  }

  public function getContent()
  {
    return $this->content;
  }

  public function canContain(AbstractBlock $block): bool
  {
    return false;
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
