<?php declare(strict_types=1);
namespace PN\B3\Rpc\CoreHandlers;
use PN\B3\App;
use PN\B3\Core\{Post, Site, User};
use PN\B3\Rpc\RpcException;
use function PN\B3\array_pluck;

class PostsHandler
{
  public function listPosts(array $params, User $user): array
  {
    $criterion = $params['type'] ?? 'latest';
    $count = $params['count'] ?? 30;
    $cursor = $params['cursor'] ?? null;

    [$posts, $cursor] = Site::getInstance()->getPosts(
      $criterion, $count, $cursor);

    $posts = array_map(function ($post) {
      return $post->toArray();
    }, $posts);

    return compact('posts', 'cursor');
  }

  public function getPost(array $params, User $user): array
  {
    if ( ! array_key_exists('id', $params)) {
      throw RpcException::invalidParams(
        'No criterion provided for post lookup.');
    }

    $post = Site::getInstance()->getPost($params['id']);
    if ($post === null) {
      throw new RpcException(1003, 'Item not found',
        'The criteria provided do not match any items.');
    }

    return $post->toArray();
  }

  public function newPost(array $params, User $user): array
  {
    $state = $params['state'];
    if ( ! Post::isValidState($state)) {
      $state = Post::STATE_DRAFT;
    }

    $post = [
      'author_id' => $user->id,
      'state' => $state,
      'slug' => $params['slug'] ?? 'sampleslug-' . uniqid(), // TODO
      'title' => $params['title'] ?? null,
      'published_at' => time(),
      'content' => $params['content'] ?? null,
      'content_type' => $params['content_type'] ?? 'markdown',
    ];

    foreach (['title', 'slug', 'content'] as $param) {
      if ($post[$param] === null) {
        throw RpcException::invalidParams(
          "Parameter `{$param}` must be provided.");
      }
    }

    $post = Post::insert(Site::getInstance()->db, $post);

    App::getInstance()->dispatchEvent('b3.posts.new', $post);
    return $post->toArray();
  }

  public function editPost(array $params, User $user): array
  {
    $id = $params['post_id'] ?? null;
    if ($id === null) {
      throw RpcException::invalidParams(
        'Parameter `post_id` must be provided.');
    }

    $post = Site::getInstance()->getPost($id);
    if ($post === null) {
      throw new RpcException(1003, 'Not found',
        'The criteria provided do not match any items.');
    }

    $updates = array_pluck($params, 'title', 'content');
    if (Post::isValidState($params['state'] ?? null)) {
      $updates['state'] = $params['state'];
    }

    $post->update(Site::getInstance()->db, $updates);

    App::getInstance()->dispatchEvent('b3.posts.edited', $post);
    return $post->toArray();
  }

  public function deletePost(array $params, User $user): bool
  {
    $id = $params['post_id'] ?? null;
    if ($id === null) {
      throw RpcException::invalidParams(
        'Parameter `post_id` must be provided.');
    }

    $post = Site::getInstance()->getPost($id);
    if ($post === null) {
      throw new RpcException(1003, 'Not found',
        'The criteria provided do not match any items.');
    }

    $post->delete(Site::getInstance()->db);

    App::getInstance()->dispatchEvent('b3.posts.deleted', $post);
    return true;
  }
}
