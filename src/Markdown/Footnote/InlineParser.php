<?php declare(strict_types=1);
namespace PN\B3\Markdown\Footnote;
use League\CommonMark\InlineParserContext;
use League\CommonMark\Inline\Parser\InlineParserInterface;

class InlineParser implements InlineParserInterface
{
  public function getCharacters(): array
  {
    return ['['];
  }

  public function parse(InlineParserContext $ctx): bool
  {
    $cursor = $ctx->getCursor();
    if ($cursor->peek(1) !== '^') {
      return false;
    }

    $state = $cursor->saveState();
    $cursor->advanceBy(2);

    $index = $cursor->match('/[0-9]+/');
    if ($index === null) {
      $cursor->restoreState($state);
      return false;
    }

    if ($cursor->getCharacter() !== ']') {
      $cursor->restoreState($state);
      return false;
    }

    $index = intval($index);
    $ctx->getContainer()
      ->appendChild(new Inline($index));

    $cursor->advance();
    return true;
  }
}
