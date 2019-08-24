<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreCms\Controllers;
use PN\B3\Rpc;
use PN\B3\Core\{Post, Site};
use PN\B3\Ext\CoreCms\TemplateRenderer;
use PN\B3\Ext\CoreRendering\Renderer;
use PN\B3\Http\{Request, Response, Session};
use PN\B3\Rpc\RpcException;
use PN\B3\Services\CsrfService;
use function PN\B3\obj_pluck;
use function PN\B3\debug_dump;

class BlogController extends BaseController
{
  public function homeAction(Request $rq): Response
  {
    return Response::redirectTo('?posts');
  }

  /** @throws RpcException */
  public function postsAction(Request $rq): Response
  {
    $response = Rpc::getInstance()->call('b3.listPosts', [
      'type' => 'latest',
      'count' => 30,
      'cursor' => $rq->query['cursor'],
    ], $rq->attributes['auth.user']);
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
      $post = Rpc::getInstance()->call('b3.newPost', [
        'state' => $post['state'] ?? Post::STATE_DRAFT,
        'title' => $post['title'],
        'content' => $post['content'],
        'content_type' => 'markdown',
      ], $rq->attributes['auth.user']);
    } catch (RpcException $exc) {
      $error = $exc->getData();
      return TemplateRenderer::renderResponse(
        'edit_post.html', compact('post', 'error'));
    }

    Session::setFlash('new_post', obj_pluck($post, 'id', 'title'));
    return Response::redirectTo('?posts');
  }

  /** @throws RpcException */
  public function postsEditAction(Request $rq): Response
  {
    if ( ! $rq->query->has('id')) {
      throw RpcException::withData('missing_id');
    }

    $id = intval($rq->query['id'], 10);
    $post = Rpc::getInstance()->call('b3.getPost', ['id' => $id],
      $rq->attributes['auth.user']);

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
      $update = obj_pluck($post, 'title', 'content', 'state');
      $update['post_id'] = $post->id;
      Rpc::getInstance()->call('b3.editPost', $update,
        $rq->attributes['auth.user']);
    } catch (RpcException $exc) {
      $error = $exc->getData();
      return TemplateRenderer::renderResponse(
        'edit_post.html', compact('post', 'error'));
    }

    Session::setFlash('edited_post', obj_pluck($post, 'id', 'title'));
    return Response::redirectTo('?posts');
  }

  public function postsDeleteAction(Request $rq): Response
  {
    if ( ! $rq->query->has('id')) {
      throw RpcException::withData('missing_id');
    }

    $id = intval($rq->query['id'], 10);
    $post = Rpc::getInstance()->call('b3.getPost', ['id' => $id],
      $rq->attributes['auth.user']);

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
      Rpc::getInstance()->call('b3.deletePost', ['post_id' => $post->id],
        $rq->attributes['auth.user']);
    } catch (RpcException $exc) {
      $error = $exc->getData();
      return TemplateRenderer::renderResponse(
        'delete_post.html', compact('post', 'error'));
    }

    Session::setFlash('deleted_post', obj_pluck($post, 'title'));
    return Response::redirectTo('?posts');
  }

  public function postsPreviewAction(Request $rq): Response
  {
    if ( ! $rq->attributes['csrf.passed']) {
      throw new RpcException(-1, 'Csrf', 'csrf');
    }

    CsrfService::getInstance()->reinstantiateToken($rq->form['_csrf']);

    $post = new Post($rq->form->pluck('title', 'content'));
    $post->contentType = 'markdown';

    $phantomName = Renderer::getInstance()->buildPhantomPost($post);
    $url = Site::getInstance()->getBaseUrl() . '/' . $phantomName;
    return Response::redirectTo($url);
  }

  public function postsShowAction(Request $rq): Response
  {
    if ( ! $rq->query->has('id')) {
      throw RpcException::withData('missing_id');
    }

    $id = intval($rq->query['id'], 10);
    $post = Rpc::getInstance()->call('b3.getPost', ['id' => $id],
      $rq->attributes['auth.user']);

    $site = Site::getInstance();
    $url = $site->getBaseUrl() . '/' . $post->getUrl();
    return Response::redirectTo($url);
  }
}
