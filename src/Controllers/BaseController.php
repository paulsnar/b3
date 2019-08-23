<?php declare(strict_types=1);
namespace PN\B3\Controllers;
use PN\B3\Http\{Request, Response, ResponseException};
use PN\B3\Http\Middleware\{BaseMiddleware as Middleware,
  Registry as MiddlewareRegistry};

abstract class BaseController
{
  protected function getGlobalMiddleware(): array
  {
    return ['b3.csrf'];
  }

  public function dispatch(Request $request, string $action): Response
  {
    $registry = MiddlewareRegistry::getInstance();

    $mw = array_map([$registry, 'lookup'], $this->getGlobalMiddleware());

    $mwBefore = array_filter($mw, function ($mw) {
      return $mw->timing === Middleware::RUN_BEFORE;
    });
    $mwAfter = array_filter($mw, function ($mw) {
      return $mw->timing === Middleware::RUN_AFTER;
    });

    usort($mwBefore, function ($a, $b) {
      return $a->priority <=> $b->priority;
    });
    usort($mwAfter, function ($a, $b) {
      return $a->priority <=> $b->priority;
    });

    $response = null;

    foreach ($mwBefore as $middleware) {
      $response = $middleware->invoke($request, $response);
    }

    if ($response !== null) {
      return $response;
    }

    $response = $this->$action($request);

    foreach ($mwAfter as $middleware) {
      $response = $middleware->invoke($request, $response);
      if ($newResponse === null) {
        throw new \RuntimeException(
          "RUN_AFTER middleware `{$middleware->name}` returned null response");
      }
    }

    return $response;
  }

  protected function invokeMiddleware(
    string $name,
    Request $rq,
    ?Response $resp = null
  ) {
    $middleware = MiddlewareRegistry::getInstance()->lookup($name);
    $response = $middleware->invoke($rq, $resp);
    if ($response !== null) {
      throw new ResponseException($response);
    }
  }
}
