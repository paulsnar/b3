<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreRpc\Handlers;
use PN\B3\Core\{Post, Site};

class PostsHandler extends BaseHandler
{
  const METHOD_MAP = [
    'b3.listPosts' => 'listPosts',
  ];

  public function listPosts(array $params): array
  {
    $this->checkAuth($params['auth_token'] ?? null);

    $criterion = $params['type'] ?? 'latest';
    $count = $params['count'] ?? 30;
    $cursor = $params['cursor'] ?? null;

    [$posts, $cursor] = Site::getInstance()->getPosts($criterion, $count, $cursor);
    $posts = array_map(function ($post) {
      return $post->toArray();
    }, $posts);
    return compact('posts', 'cursor');

  }
}
