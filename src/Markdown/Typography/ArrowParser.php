<?php declare(strict_types=1);
namespace PN\B3\Markdown\Typography;
use League\CommonMark\InlineParserContext;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Inline\Parser\InlineParserInterface;

class ArrowParser implements InlineParserInterface
{
  public function getCharacters(): array
  {
    return ['-', '=', '<'];
  }

  public function parse(InlineParserContext $context): bool
  {
    $container = $context->getContainer();
    if ($container->isCode()) {
      return false;
    }

    return $this->tryArrow($context, 2) || $this->tryArrow($context, 3);
  }

  /** @see http://xahlee.info/comp/unicode_arrows.html */
  protected const ARROWS = [
    '<-' => "\u{2190}", // ← LEFTWARDS ARROW
    '->' => "\u{2192}", // → RIGHTWARDS ARROW
    '<=' => "\u{21d0}", // ⇐ LEFTWARDS DOUBLE ARROW
    '=>' => "\u{21d2}", // ⇒ RIGHTWARDS DOUBLE ARROW

    '<->' => "\u{2194}", // ↔ LEFT RIGHT ARROW
    '<=>' => "\u{21d4}", // ⇔ LEFT RIGHT DOUBLE ARROW
  ];

  protected function tryArrow(InlineParserContext $context, int $length): bool
  {
    $cursor = $context->getCursor();
    $arrow = $cursor->getCharacter();
    for ($i = 1; $i < $length; $i += 1) {
      $arrow .= $cursor->peek($i);
    }

    if (array_key_exists($arrow, static::ARROWS)) {
      $context->getContainer()->appendChild(new Text(static::ARROWS[$arrow]));
      $cursor->advanceBy($length);
      return true;
    }

    return false;
  }
}
