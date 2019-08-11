<?php declare(strict_types=1);
namespace PN\B3\Markdown\Footnote;
use League\CommonMark\Inline\Element\AbstractInline;

class Inline extends AbstractInline
{
  public function __construct(int $index)
  {
    $this->data['index'] = $index;
  }
}
