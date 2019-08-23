<?php declare(strict_types=1);
namespace PN\B3\Dispatch;
use PN\B3\Http\{Request, Response};

interface HandlerInterface
{
  public function handle(Request $rq): Response;
}
