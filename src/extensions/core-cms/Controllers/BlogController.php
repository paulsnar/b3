<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreCms\Controllers;
use PN\B3\Rpc;
use PN\B3\Core\Post;
use PN\B3\Http\{Request, Response, Session};
use PN\B3\Rpc\RpcException;
use PN\B3\Ext\CoreCms\TemplateRenderer;
use function PN\B3\array_pluck;

class BlogController extends BaseController
{
  public function homeAction(Request $rq): Response
  {
    return Response::redirectTo('?posts');
  }

  /** @throws RpcException */
  public function postsAction(Request $rq): Response
  {
    $response = $this->callRpc($rq, 'b3.listPosts', [
      'type' => 'latest',
      'count' => 30,
      'cursor' => $rq->query['cursor'],
    ]);
    return TemplateRenderer::renderResponse('posts.html', $response);
  }

  public function postsNewAction(Request $rq): Response
  {
    if ($rq->method === 'GET') {
      return TemplateRenderer::renderResponse(
        'edit_post.html', ['post' => null]);
    }

    $post = $rq->form->pluck('title', 'content');
    if (Post::isValidState($rq->form['state'])) {
      $post['state'] = $rq->form['state'];
    }

    if ( ! $rq->attributes['csrf.passed']) {
      $error = 'csrf';
      return TemplateRenderer::renderResponse(
        'edit_post.html', compact('post', 'error'));
    }

    try {
      $post = $this->callRpc($rq, 'b3.newPost', [
        'state' => $post['state'] ?? Post::STATE_DRAFT,
        'title' => $post['title'],
        'content' => $post['content'],
        'content_type' => 'markdown',
      ]);
    } catch (RpcException $exc) {
      $error = $exc->getData();
      return TemplateRenderer::renderResponse(
        'edit_post.html', compact('post', 'error'));
    }

    Session::setFlash('new_post', array_pluck($post, 'id', 'title'));
    return Response::redirectTo('?posts');
  }

  /** @throws RpcException */
  public function postsEditAction(Request $rq): Response
  {
    if ( ! $rq->query->has('id')) {
      throw RpcException::withData('missing_id');
    }

    $id = intval($rq->query['id'], 10);
    $post = $this->callRpc($rq, 'b3.getPost', ['post_id' => $id]);

    if ($rq->method === 'GET') {
      return TemplateRenderer::renderResponse(
        'edit_post.html', compact('post'));
    }

    $post->title = $rq->form['title'];
    $post->content = $rq->form['content'];
    if (Post::isValidState($rq->form['state'])) {
      $post->state = $rq->form['state'];
    }

    if ( ! $rq->attributes['csrf.passed']) {
      $error = 'csrf';
      return TemplateRenderer::renderResponse(
        'edit_post.html', compact('post', 'error'));
    }

    try {
      $this->callRpc($rq, 'b3.editPost', [
        'post_id' => $post->id,
        'title' => $post->title,
        'content' => $post->content,
        'state' => $post->state,
      ]);
    } catch (RpcException $exc) {
      $error = $exc->getData();
      return TemplateRenderer::renderResponse(
        'edit_post.html', compact('post', 'error'));
    }

    Session::setFlash(
      'edited_post', ['id' => $post->id, 'title' => $post->title]);
    return Response::redirectTo('?posts');
  }

  public function postsDeleteAction(Request $rq): Response
  {
    if ( ! $rq->query->has('id')) {
      throw RpcException::withData('missing_id');
    }

    $id = intval($rq->query['id'], 10);
    $post = $this->callRpc($rq, 'b3.getPost', ['post_id' => $id]);

    if ($rq->method === 'GET') {
      return TemplateRenderer::renderResponse(
        'delete_post.html', compact('post'));
    }

    if ( ! $rq->attributes['csrf.passed']) {
      $error = 'csrf';
      return TemplateRenderer::renderResponse(
        'delete_post.html', compact('post', 'error'));
    }

    try {
      $this->callRpc($rq, 'b3.deletePost', ['post_id' => $post->id]);
    } catch (RpcException $exc) {
      $error = $exc->getData();
      return TemplateRenderer::renderResponse(
        'delete_post.html', compact('post', 'error'));
    }

    Session::setFlash('deleted_post', ['title' => $post->title]);
    return Response::redirectTo('?posts');
  }
}
