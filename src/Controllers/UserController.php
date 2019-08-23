<?php declare(strict_types=1);
namespace PN\B3\Controllers;
use PN\B3\Config;
use PN\B3\Http\{Request, Response};

class UserController extends BaseController
{
  const NEEDS_AUTHENTICATION = true;

  public function userAction(Request $rq): Response
  {
    if ($rq->query->has('id')) {
      return $this->renderTemplateResponse('blog/error.html',
        ['error' => 'not_implemented']);
    }

    $user = $rq->attributes['auth.user'];

    if ($rq->method === 'GET') {
      return $this->renderTemplateResponse('blog/user.html', compact('user'));
    }

    if ( ! $this->checkCsrfToken($rq->form['_csrf'])) {
      $error = 'csrf';
      return $this->renderTemplateResponse('blog/user.html',
        compact('user', 'csrf'));
    }

    $update = [ ];
    if ($rq->form->get('username', $user->username) !== $user->username) {
      $update['username'] = $rq->form['username'];
    }

    if ($rq->form->get('password', '') !== '') {
      $update['password'] =
        password_hash($rq->form['password'], PASSWORD_DEFAULT);
    }

    if ($update === [ ]) {
      $updated = false;
    } else {
      $user->update(Config::getDb(), $update);
      $updated = true;
    }

    return $this->renderTemplateResponse('blog/user.html',
      compact('user', 'updated'));
  }
}
