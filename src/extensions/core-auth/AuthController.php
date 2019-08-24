<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreAuth;
use PN\B3\Controllers\BaseController;
use PN\B3\Http\{Request, Response, Session};
use PN\B3\Services\SecurityService;

class AuthController extends BaseController
{
  public function loginAction(Request $rq): Response
  {
    $then = $rq->query['then'];
    $target = '?' . ($then ?: 'home');

    $sec = SecurityService::getInstance();
    if ($sec->checkAuthentication($rq)) {
      return Response::redirectTo($target);
    }

    if ($rq->method === 'GET') {
      return TemplateRenderer::renderResponse('login.html', compact('then'));
    }

    $username = $rq->form['username'];

    if ( ! $rq->attributes['csrf.passed']) {
      $error = 'csrf';
      return TemplateRenderer::renderResponse(
        'login.html', compact('then', 'username', 'error'));
    }

    $login = $rq->form->pluck('username', 'password');
    $result = $sec->attemptLogin($login);
    if ($result === null) {
      $error = 'credentials';
      return TemplateRenderer::renderResponse(
        'login.html', compact('then', 'username', 'error'));
    }

    $resp = Response::redirectTo($target);
    $resp->cookies[] = $result['token_cookie'];
    return $resp;
  }

  public function logoutAction(Request $rq): Response
  {
    Session::setFlash('logout', true);
    return SecurityService::getInstance()->performLogout($rq);
  }
}
