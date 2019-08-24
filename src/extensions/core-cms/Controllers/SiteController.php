<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreCms\Controllers;
use PN\B3\Ext\CoreCms\TemplateRenderer;
use PN\B3\Http\{Request, Response, Session};
use PN\B3\Rpc;
use function PN\B3\{array_pluck, obj_pluck};

class SiteController extends BaseController
{
  public function homeAction(Request $rq): Response
  {
    $sites = Rpc::getInstance()->call(
      'b3.listSites', [ ], $rq->attributes['auth.user']);
    return TemplateRenderer::renderResponse('sites.html', compact('sites'));
  }

  public function sitesNewAction(Request $rq): Response
  {
    $ctx = ['site' => null];

    if ($rq->method === 'GET') {
      return TemplateRenderer::renderResponse('edit_site.html', $ctx);
    }

    $site = $rq->form->pluck('title', 'base_url', 'target_path');
    $ctx['site'] =& $site;

    if ( ! $rq->attributes['csrf.passed']) {
      $ctx['error'] = $csrf;
      return TemplateRenderer::renderResponse('edit_site.html', $ctx);
    }

    try {
      $site = Rpc::getInstance()->call(
        'b3.newSite', array_pluck($site, 'title', 'base_url', 'target_path'),
        $rq->attributes['auth.user']);
    } catch (RpcException $exc) {
      $ctx['error'] = $exc->getData();
      return TemplateRenderer::renderResponse('edit_site.html', $ctx);
    }

    Session::setFlash('new_site', true);
    return Response::redirectTo('?posts&site_id=' . $site->id);
  }

  public function sitesEditAction(Request $rq): Response
  {
    if ( ! $rq->query->has('id')) {
      throw new CmsException('missing_id');
    }

    $id = intval($rq->query['id'], 10);
    $site = Rpc::getInstance()->call('b3.getSite', ['id' => $id],
      $rq->attributes['auth.user']);

    $ctx = ['site' => $site, 'site_id' => $site->id];

    if ($rq->method === 'GET') {
      return TemplateRenderer::renderResponse('edit_site.html', $ctx);
    }

    $site->title = $rq->form['title'];
    $site->base_url = $rq->form['base_url'];
    $site->target_path = $rq->form['target_path'];

    if ( ! $rq->attributes['csrf.passed']) {
      $ctx['error'] = 'csrf';
      return TemplateRenderer::renderResponse('edit_site.html', $ctx);
    }

    try {
      $update = obj_pluck($site, 'title', 'base_url', 'target_path');
      $update['site_id'] = $site->id;
      Rpc::getInstance()->call('b3.editSite', $update,
        $rq->attributes['auth.user']);
    } catch (RpcException $exc) {
      $ctx['error'] = $exc->getData();
      return TemplateRenderer::renderResponse('edit_site.html', $ctx);
    }

    $ctx['updated'] = true;
    return TemplateRenderer::renderResponse('edit_site.html', $ctx);
  }
}
