<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreCms\Controllers;
use PN\B3\Rpc;
use PN\B3\Core\{Post, Site};
use PN\B3\Http\{Request, Response, Session};
use PN\B3\Rpc\RpcException;
use PN\B3\Ext\CoreCms\{CmsException, TemplateRenderer};
use function PN\B3\array_pluck;

class UserController extends BaseController
{
  public function userAction(Request $rq): Response
  {
    if ($rq->query->has('id')) {
      throw new CmsException('not_implemented');
    }

    $user = $rq->attributes['auth.user'];
    $ctx = compact('user');

    $siteId = $rq->query['site_id'];
    if ($siteId === null || ! ctype_digit($siteId)) {
      goto skip_site_id_search;
    }
    $siteId = intval($siteId, 10);
    if (Site::exists(['id' => $siteId])) {
      $ctx['site_id'] = $siteId;
    }
  skip_site_id_search:

    if ($rq->method === 'GET') {
      return TemplateRenderer::renderResponse('user.html', $ctx);
    }

    if ( ! $rq->attributes['csrf.passed']) {
      $ctx['error'] = 'csrf';
      return TemplateRenderer::renderResponse('user.html', $ctx);
    }

    $call = ['user_id' => $user->id];

    $updated = false;
    if ($rq->form->get('username', $user->username) !== $user->username) {
      $updated = true;
      $user->username = $call['username'] = $rq->form['username'];
    }

    if ($rq->form->get('password', '') !== '') {
      $updated = true;
      $call['password'] = $rq->form['password'];
    }

    if ( ! $updated) {
      $ctx['updated'] = $updated;
      return TemplateRenderer::renderResponse('user.html', $ctx);
    }

    try {
      $this->callRpc($rq, 'b3.updateUser', $call);
    } catch (RpcException $exc) {
      $ctx['error'] = $exc->getData();
      return TemplateRenderer::renderResponse('user.html', $ctx);
    }

    $ctx['updated'] = $updated;
    return TemplateRenderer::renderResponse('user.html', $ctx);
  }
}
