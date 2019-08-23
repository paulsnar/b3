<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreRpc\Handlers;
use PN\B3\Core\User;
use PN\B3\Services\SecurityService;
use PN\B3\Rpc\RpcException;

abstract class BaseHandler
{
  public static function handleCall(string $method, array $params)
  {
    static $instance;
    if ($instance === null) {
      $instance = new static();
    }

    $method = $instance::METHOD_MAP[$method];
    return $instance->$method($params);
  }

  protected function checkAuth(?string $token): User
  {
    if ($token === null) {
      throw new RpcException(
        1001, 'Unauthenticated', 'No `auth_token` was presented, but this ' .
          'request requires authentication.');
    }

    $user = SecurityService::getInstance()->verifySessionToken($token);
    if ($user === null) {
      throw new RpcException(
        1002, 'Unauthorized', 'The `auth_token` presented is not valid.');
    }

    return $user;
  }
}
