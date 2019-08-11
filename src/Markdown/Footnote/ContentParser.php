<?php declare(strict_types=1);
namespace PN\B3\Markdown\Footnote;
use League\CommonMark\{ContextInterface, Cursor};
use League\CommonMark\Block\Parser\BlockParserInterface;

class ContentParser implements BlockParserInterface
{
  public function parse(ContextInterface $ctx, Cursor $cursor): bool
  {
    if ($cursor->getCharacter() !== '[' ||
        $cursor->peek(1) !== '^') {
      return false;
    }

    $state = $cursor->saveState();
    $cursor->advanceBy(2);

    $index = $cursor->match('/[0-9]+/');
    if ($index === null ||
        $cursor->getCharacter() !== ']' ||
        $cursor->peek(1) !== ':') {
      $cursor->restoreState($state);
      return false;
    }

    $cursor->advanceBy(2);

    $ctx->addBlock(new Content(intval($index)));
    return true;
  }
}
