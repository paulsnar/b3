<?php declare(strict_types=1);
namespace PN\B3\Controllers;
use PN\B3\{App, Site};
use PN\B3\Http\{Request, Response};
use PN\B3\Models\Post;

class BlogController extends BaseController
{
  const NEEDS_AUTHENTICATION = true;

  public function homeAction(Request $rq): Response
  {
    [$posts, $postCursor] = Site::getInstance()->getPosts('latest', 15);
    return $this->renderTemplateResponse('blog/home.html', [
      'posts' => $posts,
      'posts_cursor' => $postCursor,
    ]);
  }

  public function postsAction(Request $rq): Response
  {
    $cursor = $rq->query['after'];
    [$posts, $cursor] = Site::getInstance()->getPosts('latest', 30, $cursor);
    return $this->renderTemplateResponse('blog/posts.html',
      compact('posts', 'cursor'));
  }

  public function postsNewAction(Request $rq): Response
  {
    if ($rq->method === 'GET') {
      return $this->renderTemplateResponse('blog/edit_post.html',
        ['post' => null]);
    }

    $post = $rq->form->pluck('title', 'content');
    if (Post::isValidState($rq->form['state'])) {
      $post['state'] = $rq->form['state'];
    }

    if ( ! $this->checkCsrfToken($rq->form['_csrf'])) {
      $error = 'csrf';
      return $this->renderTemplateResponse('blog/edit_post.html',
        compact('post', 'error'));
    }

    $post = Post::insert(Site::getInstance()->db, [
      'author_id' => $rq->attributes['auth.user']->id,
      'state' => $post['state'] ?? Post::STATE_DRAFT,
      'slug' => 'todo' . uniqid(), // TODO
      'title' => $post['title'],
      'published_at' => time(),
      'content' => $post['content'],
      'content_type' => 'markdown',
    ]);
    Session::setFlash('new_post',
      ['id' => $post->id, 'title' => $post->title]);
    return Response::redirectTo('?posts');
  }

  public function postsEditAction(Request $rq): Response
  {
    if ( ! $rq->query->has('id')) {
      return $this->renderTemplateResponse('blog/error.html',
        ['error' => 'missing_id']);
    }

    $id = intval($rq->query['id'], 10);
    $post = Site::getInstance()->getPost($id);
    if ($post === null) {
      return $this->renderTemplateResponse('blog/error.html',
        ['error' => 'not_found']);
    }

    if ($rq->method === 'GET') {
      return $this->renderTemplateResponse('blog/edit_post.html',
        compact('post'));
    }

    $post->title = $rq->form['title'];
    $post->content = $rq->form['content'];
    if (Post::isValidState($rq->form['state'])) {
      $post->state = $rq->form['state'];
    }

    if ( ! $this->checkCsrfToken($rq->form['_csrf'])) {
      $error = 'csrf';
      return $this->renderTemplateResponse('blog/edit_post.html',
        compact('post', 'error'));
    }

    $post->update(Site::getInstance()->db);
    Session::setFlash('edited_post',
      ['id' => $post->id, 'title' => $post->title]);
    return Response::redirectTo('?posts');
  }

  public function postsDeleteAction(Request $rq): Response
  {
    if ( ! $rq->query->has('id')) {
      return $this->renderTemplateResponse('blog/error.html',
        ['error' => 'missing_id']);
    }

    $id = intval($rq->query['id'], 10);
    $post = Site::getInstance()->getPost($id);
    if ($post === null) {
      return $this->renderTemplateResponse('blog/error.html',
        ['error' => 'not_found']);
    }

    if ($rq->method === 'GET') {
      return $this->renderTemplateResponse('blog/delete_post.html',
        compact('post'));
    }

    if ( ! $this->checkCsrfToken($rq->form['_csrf'])) {
      $error = 'csrf';
      return $this->renderTemplateResponse('blog/delete_post.html',
        compact('post', 'error'));
    }

    $post->delete(Site::getInstance()->db);
    Session::setFlash('deleted_post', ['title' => $post->title]);
    return Response::redirectTo('?posts');
  }

  public function postsPreviewAction(Request $rq): Response
  {
    // TODO
    return $this->renderTemplateResponse('blog/error.html',
      ['error' => 'not_implemented']);
  }
}
