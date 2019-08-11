<?php declare(strict_types=1);
namespace PN\B3\Core;
use PN\B3\Render\{RenderableInterface, RenderedDocument, RendererInterface};

class Index implements RenderableInterface
{
  protected $items = [ ];

  public function __construct(array $contentPieces)
  {
    foreach ($contentPieces as $item) {
      if ($item instanceof BlogPost) {
        if ( ! $item->isVisible()) {
          continue;
        }
        $this->items[] = ['is' => 'blog_post', 'item' => $item];
      }
    }
  }

  public function renderSelf(RendererInterface $renderer): RenderedDocument
  {
    $items = $this->items;
    usort($items, function ($a, $b) {
      return $b['item']->date <=> $a['item']->date;
    });

    $page = $renderer->renderTemplate('index.html', [
      'items' => $items,
    ]);
    return new RenderedDocument($this, $page);
  }
}
