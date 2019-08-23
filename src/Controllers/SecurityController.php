<?php declare(strict_types=1);
namespace PN\B3\Controllers;
use PN\B3\Http\{Request, Response, Session};
use PN\B3\Services\SecurityService;
use function PN\B3\array_pluck;

class SecurityController extends BaseController
{
  public const NEEDS_AUTHENTICATION = false;

  public function logoutAction(Request $rq): Response
  {
    Session::setFlash('logout', true);
    return SecurityService::getInstance()->performLogout($rq);
  }

  public function loginAction(Request $rq): Response
  {
    $then = $rq->query['then'];
    $target = '?' . ($then ?: 'home');

    $sec = SecurityService::getInstance();
    if ($sec->checkAuthentication($rq)) {
      return Response::redirectTo($target);
    }

    if ($rq->method !== 'POST') {
      return $this->renderTemplateResponse('auth/login.html',
        compact('then'));
    }

    $username = $rq->form['username'];

    if ( ! $this->checkCsrfToken($rq->form['_csrf'])) {
      $error = 'csrf';
      return $this->renderTemplateResponse('auth/login.html',
        compact('then', 'error', 'username'));
    }

    $login = $rq->form->pluck('username', 'password');
    $result = $sec->attemptLogin($login);
    if ($result === null) {
      $error = 'credentials';
      return $this->renderTemplateResponse('auth/login.html',
        compact('then', 'error', 'username'));
    }

    $resp = Response::redirectTo($target);
    $resp->cookies[] = $result['token_cookie'];
    return $resp;
  }
}
