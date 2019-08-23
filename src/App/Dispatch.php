<?php declare(strict_types=1);
namespace PN\B3\App;
use PN\B3\{App, Ctrl};
use PN\B3\Http\{Request, Response, Status};
use PN\B3\Services\SecurityService;
use PN\B3\Util\Singleton;

class Dispatch
{
  use Singleton;

  public $request, $response;

  public function dispatch(array $table, ?string $action = null)
  {
    $rq = $this->request = Request::fromGlobals();
    if ($action === null) {
      $action = $rq->action ?: 'home';
    }

    $handler = $table[$action] ?? null;
    if ($handler === null) {
      return Response::withText('Sorry, not found.', Status::NOT_FOUND);
    }

    [$class, $method] = $handler;
    if (method_exists($class, 'getInstance')) {
      $handler = $class::getInstance();
    } else {
      $handler = new $class();
    }

    $needsAuthentication = true;
    if (defined($class . '::NEEDS_AUTHENTICATION')) {
      $needsAuthentication = $class::NEEDS_AUTHENTICATION;
    } else if (method_exists($handler, 'needsAuthentication')) {
      $needsAuthentication = $handler->needsAuthentication($rq, $action);
    }

    if ($needsAuthentication) {
      $resp = SecurityService::getInstance()->authenticate($rq);
      if ($resp !== null) {
        return $resp;
      }
    }

    return $handler->$method($rq);
  }
}
