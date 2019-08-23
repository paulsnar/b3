<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreMiddleware;
use PN\B3\Http\{Request, Response};
use PN\B3\Http\Middleware\BaseMiddleware;
use PN\B3\Services\CsrfService;

class CsrfMiddleware extends BaseMiddleware
{
  public $name = 'b3.csrf';

  public function invoke(Request $rq, ?Response $resp): ?Response
  {
    if ($rq->method === 'GET' || $rq->method === 'HEAD') {
      return $resp;
    }

    $passed = false;
    if ($rq->form->has('_csrf')) {
      $token = $rq->form['_csrf'];
      $passed = CsrfService::getInstance()->checkToken($token);
    }

    $rq->attributes['csrf.passed'] = $passed;

    return $resp;
  }
}
