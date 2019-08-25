<?php declare(strict_types=1);
namespace PN\B3\Rpc\CoreHandlers;
use PN\B3\App;
use PN\B3\Core\{Post, Site, User};
use PN\B3\Rpc\RpcException;
use function PN\B3\{array_index, array_pluck};

class PostsHandler
{
  public function listPosts(array $params, User $user): array
  {
    $site = $params['site_id'] ?? null;
    if ($site === null) {
      throw RpcException::invalidParams('No site_id specified.');
    }

    $posts = Post::selectAll([
      'site_id' => $site,
      'state' => array_index($params, 'state', Post::STATE_PUBLISHED),
      'published_before' => array_index($params, 'cursor', null),
    ]);

    $cursor = null;
    if ($posts !== [ ]) {
      $cursor = $posts[count($posts) - 1]->published_at->getTimestamp();
      $hasMore = Post::exists(['published_at' => ['<', $cursor]]);
      if ( ! $hasMore) {
        $cursor = null;
      }
    }

    return compact('posts', 'cursor');
  }

  public function getPost(array $params, User $user): Post
  {
    $id = $params['id'] ?? null;
    if ($id === null) {
      throw RpcException::invalidParams(
        'No criterion provided for post lookup.');
    }

    $post = Post::lookup(['id' => $id]);
    if ($post === null) {
      throw new RpcException(1003, 'Item not found',
        'The criteria provided do not match any items.');
    }

    return $post;
  }

  public function newPost(array $params, User $user): Post
  {
    $site = $params['site_id'] ?? null;
    if ($site === null) {
      throw RpcException::invalidParams('No site_id provided.');
    }
    $site = Site::lookup(['id' => $site]);
    if ($site === null) {
      throw new RpcException(1003, 'Not found',
        'site_id does not correspond to a known site.');
    }

    foreach (['title', 'content'] as $field) {
      if ( ! array_key_exists($field, $params)) {
        throw RpcException::invalidParams("Field `{$field}` not present.");
      }
    }

    $state = $params['state'] ?? Post::STATE_DRAFT;
    if ( ! Post::isValidState($state)) {
      throw RpcException::invalidParams(
        "'{$state}' is not a valid post state.");
      $state = Post::STATE_DRAFT;
    }

    $slug = $params['slug'] ?? null;
    if ($slug === null) {
      $slug = $params['title'];
      $slug = preg_replace('/[^A-Za-z0-9]+/', '-', $slug);
      $slug = strtolower($slug);
    }

    $post = [
      'author_id' => $user->id,
      'site_id' => $site->id,
      'state' => $state,
      'slug' => $slug,
      'title' => $params['title'],
      'published_at' => time(),
      'content' => $params['content'],
      'content_type' => $params['content_type'] ?? 'markdown',
    ];
    $post = Post::insert($post);

    App::getInstance()->dispatchEvent('b3.posts.new', $site, $post);
    return $post;
  }

  public function editPost(array $params, User $user): Post
  {
    $id = $params['post_id'] ?? null;
    if ($id === null) {
      throw RpcException::invalidParams(
        'Parameter `post_id` must be provided.');
    }

    $post = Post::lookup(['id' => $id]);
    if ($post === null) {
      throw new RpcException(1003, 'Not found',
        'The criteria provided do not match any items.');
    }

    $updates = array_pluck($params, 'title', 'content', 'slug');
    if (Post::isValidState($params['state'] ?? null)) {
      $updates['state'] = $params['state'];
    }
    $updates['modified_at'] = time();

    $post->update($updates);

    $site = Site::lookup(['id' => $post->site_id]);
    App::getInstance()->dispatchEvent('b3.posts.edited', $site, $post);
    return $post;
  }

  public function deletePost(array $params, User $user): bool
  {
    $id = $params['post_id'] ?? null;
    if ($id === null) {
      throw RpcException::invalidParams(
        'Parameter `post_id` must be provided.');
    }

    $post = Post::lookup(['id' => $id]);
    if ($post === null) {
      throw new RpcException(1003, 'Not found',
        'The criteria provided do not match any items.');
    }

    $post->delete();

    $site = Site::lookup(['id' => $post->site_id]);
    App::getInstance()->dispatchEvent('b3.posts.deleted', $site, $post);
    return true;
  }

  public function rebuild(array $params, User $user): bool
  {
    $site = $params['site_id'] ?? null;
    $renderer = \PN\B3\Ext\CoreRendering\Renderer::getInstance();

    if ($site === null) {
      $sites = Site::select([ ]);
    } else {
      $site = Site::lookup(['id' => $site]);
      if ($site === null) {
        throw new RpcException(1003, 'Not found',
          'site_id does not correspond to a known site.');
      }
      $sites = [$site];
    }

    foreach ($sites as $site) {
      $renderer->buildIndexes($site);
      $posts = Post::select([
        'site_id' => $site->id,
        'state' => Post::STATE_PUBLISHED,
      ]);
      foreach ($posts as $post) {
        $renderer->buildPost($site, $post);
      }
    }

    return true;
  }
}
