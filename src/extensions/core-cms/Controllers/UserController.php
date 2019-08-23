<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreCms\Controllers;
use PN\B3\Rpc;
use PN\B3\Core\{Post, Site};
use PN\B3\Http\{Request, Response, Session};
use PN\B3\Rpc\RpcException;
use PN\B3\Ext\CoreCms\TemplateRenderer;
use function PN\B3\array_pluck;

class UserController extends BaseController
{
  public function userAction(Request $rq): Response
  {
    if ($rq->query->has('id')) {
      return TemplateRenderer::renderResponse(
        'error.html', ['error' => 'not_implemented']);
    }

    $user = $rq->attributes['auth.user'];

    if ($rq->method === 'GET') {
      return TemplateRenderer::renderResponse('user.html', compact('user'));
    }

    if ( ! $rq->attributes['csrf.passed']) {
      $error = 'csrf';
      return TemplateRenderer::renderResponse(
        'user.html', compact('user', 'error'));
    }

    $call = ['user_id' => $user['id']];

    $updated = false;
    if ($rq->form->get('username', $user->username) !== $user->username) {
      $updated = true;
      $user['username'] = $call['username'] = $rq->form['username'];
    }

    if ($rq->form->get('password', '') !== '') {
      $updated = true;
      $call['password'] = $rq->form['password'];
    }

    if ($updated === false) {
      return TemplateRenderer::renderResponse(
        'user.html', compact('user', 'updated'));
    }

    try {
      $this->callRpc($rq, 'b3.updateUser', $call);
    } catch (RpcException $exc) {
      $error = $exc->getData();
      return TemplateRenderer::renderResponse(
        'user.html', compact('user', 'error'));
    }

    return TemplateRenderer::renderResponse(
      'user.html', compact('user', 'updated'));
  }
}
