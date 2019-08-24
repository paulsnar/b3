<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreCms\Controllers;
use PN\B3\Rpc;
use PN\B3\Core\{Post, Site};
use PN\B3\Ext\CoreCms\{CmsException, TemplateRenderer};
use PN\B3\Ext\CoreRendering\Renderer;
use PN\B3\Http\{Request, Response, Session};
use PN\B3\Rpc\RpcException;
use PN\B3\Services\CsrfService;
use function PN\B3\{obj_pluck, url_join};

class BlogController extends BaseController
{
  /** @throws RpcException */
  public function postsAction(Request $rq): Response
  {
    $siteId = $this->requireSiteId($rq);

    $response = Rpc::getInstance()->call('b3.listPosts', [
      'site_id' => $siteId,
      'state' => null,
      'count' => 30,
      'cursor' => $rq->query['cursor'],
    ], $rq->attributes['auth.user']);

    return TemplateRenderer::renderResponse('posts.html',
      ['site_id' => $siteId] + $response);
  }

  public function postsNewAction(Request $rq): Response
  {
    $siteId = $this->requireSiteId($rq);
    $ctx = ['site_id' => $siteId, 'post' => null];

    if ($rq->method === 'GET') {
      return TemplateRenderer::renderResponse('edit_post.html', $ctx);
    }

    $post = $rq->form->pluck('title', 'content');
    $ctx['post'] =& $post;
    if (Post::isValidState($rq->form['state'])) {
      $post['state'] = $rq->form['state'];
    }

    if ( ! $rq->attributes['csrf.passed']) {
      $ctx['error'] = 'csrf';
      return TemplateRenderer::renderResponse('edit_post.html', $ctx);
    }

    try {
      $post = Rpc::getInstance()->call('b3.newPost', [
        'site_id' => $siteId,
        'state' => $post['state'] ?? Post::STATE_DRAFT,
        'title' => $post['title'],
        'content' => $post['content'],
        'content_type' => 'markdown',
      ], $rq->attributes['auth.user']);
    } catch (RpcException $exc) {
      $ctx['error'] = $exc->getData();
      return TemplateRenderer::renderResponse('edit_post.html', $ctx);
    }

    Session::setFlash('new_post', obj_pluck($post, 'id', 'title'));
    return Response::redirectTo('?posts&site_id=' . $siteId);
  }

  /** @throws RpcException */
  public function postsEditAction(Request $rq): Response
  {
    if ( ! $rq->query->has('id')) {
      throw new CmsException('missing_id');
    }

    $id = intval($rq->query['id'], 10);
    $post = Rpc::getInstance()->call('b3.getPost', ['id' => $id],
      $rq->attributes['auth.user']);

    $ctx = ['post' => $post, 'site_id' => $post->site_id];

    if ($rq->method === 'GET') {
      return TemplateRenderer::renderResponse('edit_post.html', $ctx);
    }

    $post->title = $rq->form['title'];
    $post->content = $rq->form['content'];
    if (Post::isValidState($rq->form['state'])) {
      $post->state = $rq->form['state'];
    }

    if ( ! $rq->attributes['csrf.passed']) {
      $ctx['error'] = 'csrf';
      return TemplateRenderer::renderResponse('edit_post.html', $ctx);
    }

    try {
      $update = obj_pluck($post, 'title', 'content', 'state');
      $update['post_id'] = $post->id;
      Rpc::getInstance()->call('b3.editPost', $update,
        $rq->attributes['auth.user']);
    } catch (RpcException $exc) {
      $ctx['error'] = $exc->getData();
      return TemplateRenderer::renderResponse('edit_post.html', $ctx);
    }

    Session::setFlash('edited_post', obj_pluck($post, 'id', 'title'));
    return Response::redirectTo('?posts&site_id=' . $post->site_id);
  }

  public function postsDeleteAction(Request $rq): Response
  {
    if ( ! $rq->query->has('id')) {
      throw RpcException::withData('missing_id');
    }

    $id = intval($rq->query['id'], 10);
    $post = Rpc::getInstance()->call('b3.getPost', ['id' => $id],
      $rq->attributes['auth.user']);

    $ctx = ['post' => $post, 'site_id' => $post->site_id];

    if ($rq->method === 'GET') {
      return TemplateRenderer::renderResponse('delete_post.html', $ctx);
    }

    if ( ! $rq->attributes['csrf.passed']) {
      $ctx['error'] = 'csrf';
      return TemplateRenderer::renderResponse('delete_post.html', $ctx);
    }

    try {
      Rpc::getInstance()->call('b3.deletePost', ['post_id' => $post->id],
        $rq->attributes['auth.user']);
    } catch (RpcException $exc) {
      $ctx['error'] = $exc->getData();
      return TemplateRenderer::renderResponse('delete_post.html', $ctx);
    }

    Session::setFlash('deleted_post', obj_pluck($post, 'title'));
    return Response::redirectTo('?posts&site_id=' . $post->site_id);
  }

  public function postsPreviewAction(Request $rq): Response
  {
    if ( ! $rq->attributes['csrf.passed']) {
      throw new CmsException('csrf');
    }
    CsrfService::getInstance()->reinstantiateToken($rq->form['_csrf']);

    $id = $this->requireSiteId($rq, false);
    $site = Site::lookup(['id' => $id]);
    if ($site === null) {
      throw new CmsException('invalid_site_id');
    }

    $post = new Post($rq->form->pluck('title', 'content'));
    $post->content_type = 'markdown';

    $phantomName = Renderer::getInstance()->buildPhantomPost($site, $post);
    $url = url_join($site->base_url, $phantomName);
    return Response::redirectTo($url);
  }

  public function postsShowAction(Request $rq): Response
  {
    if ($rq->query->has('id')) {
      $post = Post::lookup(['id' => intval($rq->query['id'], 10)]);
      if ($post === null) {
        throw new CmsException('not_found');
      }
      $site = Site::lookup(['id' => $post->site_id]);
      if ($site === null) {
        throw new CmsException('not_found');
      }
      $target = url_join($site->base_url, $post->url);
    } else if ($rq->query->has('site_id')) {
      $site = Site::lookup(['id' => intval($rq->query['site_id'], 10)]);
      if ($site === null) {
        throw new CmsException('not_found');
      }
      $target = $site->base_url;
    } else {
      throw new CmsException('missing_id');
    }

    return Response::redirectTo($target);
  }

  public function rebuildAction(Request $rq): Response
  {
    $ctx = ['site_id' => $this->requireSiteId($rq)];

    if ($rq->method === 'GET') {
      return TemplateRenderer::renderResponse('rebuild.html', $ctx);
    }

    if ( ! $rq->attributes['csrf.passed']) {
      $ctx['error'] = 'csrf';
      return TemplateRenderer::renderResponse('rebuild.html', $ctx);
    }

    Rpc::getInstance()->call('b3.rebuild', [], $rq->attributes['auth.user']);

    Session::setFlash('rebuilt', true);
    return Response::redirectTo('?posts&site_id=' . $ctx['site_id']);
  }
}
