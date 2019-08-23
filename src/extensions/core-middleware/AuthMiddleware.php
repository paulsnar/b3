<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreMiddleware;
use PN\B3\Http\{Request, Response, ResponseException};
use PN\B3\Http\Middleware\BaseMiddleware;
use PN\B3\Services\SecurityService;

class AuthMiddleware extends BaseMiddleware
{
  public $name = 'b3.auth';
  public $priority = 850;

  public function invoke(Request $rq, ?Response $resp): ?Response
  {
    if ( ! SecurityService::getInstance()->checkAuthentication($rq)) {
      $then = $rq->action;
      if ( ! $rq->query->isEmpty()) {
        $then .= '&' . http_build_query($rq->query->toArray());
      }
      if ($then !== '') {
        $then = '&then=' . urlencode($then);
      }
      $resp = Response::redirectTo('?login' . $then);
      throw new ResponseException($resp);
    }

    return $resp;
  }
}
