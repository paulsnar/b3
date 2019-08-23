<?php declare(strict_types=1);
namespace PN\B3\Http;

interface HttpSerializable
{
  public function serializeHttp(Request $rq): Response;
}
