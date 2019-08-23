<?php declare(strict_types=1);
namespace PN\B3\Rpc;

interface OptionalAuthenticationAware
{
  public function callNeedsAuthentication(
    string $rpcMethod,
    string $objectMethod,
    array $params
  ): bool;
}
