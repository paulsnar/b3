<?php declare(strict_types=1);
namespace PN\B3\Core\Feed;
use PN\B3\Core\BlogPost;
use PN\B3\Core\Address\UrlAddressableInterface;
use function PN\B3\url_absolute;

class JsonFeed implements UrlAddressableInterface
{
  protected $items = [ ];

  public function __construct(array $contentPieces)
  {
    foreach ($contentPieces as $item) {
      if ($item instanceof BlogPost) {
        if ( ! $item->isVisible()) {
          continue;
        }

        $this->items[] = $item;
      }
    }
  }

  public function renderToText(): string
  {
    $feed = [
      'version' => 'https://jsonfeed.org/version/1',
      'title' => "paulsnar's blog",
      'home_page_url' => url_absolute('/'),
      'feed_url' => url_absolute($this->getUrl()),
      'author' => ['name' => 'paulsnar'],
    ];

    $items = [ ];
    foreach ($this->items as $item) {
      $items[] = [
        'id' => url_absolute($item->getUrl()),
        'url' => url_absolute($item->getUrl()),
        'title' => $item->metadata['title'],
        'date_published' => $item->date->format(DATE_ATOM),
        'content_html' => $item->contentHtml,
      ];
    }

    $feed['items'] = $items;

    return json_encode($feed, JSON_UNESCAPED_SLASHES);
  }

  public function getUrl(): string
  {
    return '/feed.json';
  }
}
