<?php declare(strict_types=1);
namespace PN\Blog\Markdown\Footnote;
use League\CommonMark\Block\Element\Paragraph;

class Content extends Paragraph
{
  public function __construct(int $index)
  {
    parent::__construct();
    $this->data['index'] = $index;
  }
}
