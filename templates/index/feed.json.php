<?php declare(strict_types=1);

echo json_encode([
  'version' => 'https://jsonfeed.org/version/1',
  'title' => $site['title'],
  'home_page_url' => $site['url'],
  'feed_url' => $url('/feed.json'),
  'author' => $site['author'],
  'items' => array_map(function ($post) use ($url) {
    $item = [
      'id' => $url($post['url']),
      'url' => $url($post['url']),
      'title' => $post['title'],
      'date_published' => $post['published_at']->format(DATE_ATOM),
    ];
    if (array_key_exists('modified_at', $post)) {
      $item['date_modified'] = $post['modified_at']->format(DATE_ATOM);
    }
    $item['content_html'] = $post['body'];
    return $item;
  }, $posts),
], JSON_UNESCAPED_SLASHES);

