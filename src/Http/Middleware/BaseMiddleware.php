<?php declare(strict_types=1);
namespace PN\B3\Http\Middleware;
use PN\B3\Http\{Request, Response};

abstract class BaseMiddleware
{
  public const
    RUN_BEFORE = 1,
    RUN_AFTER = 2;

  public $name;
  public $timing = self::RUN_BEFORE;
  public $priority = 1000;

  abstract public function invoke(Request $rq, ?Response $resp): ?Response;
}
