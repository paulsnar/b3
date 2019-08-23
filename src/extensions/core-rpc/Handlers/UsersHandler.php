<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreRpc\Handlers;
use PN\B3\Config;
use PN\B3\Rpc\RpcException;
use PN\B3\Services\SecurityService;
use function PN\B3\array_pluck;

class UsersHandler extends BaseHandler
{
  const METHOD_MAP = [
    'b3.checkAuth' => 'b3CheckAuth',
    'b3.login' => 'login',
    'b3.updateUser' => 'updateUser',
  ];

  public function updateUser(array $params)
  {
    $user = $this->checkAuth($params['auth_token'] ?? null);
    if ($params['user_id'] !== $user->id) {
      throw new RpcException(1002, 'Unauthorized',
        'Cannot update attributes for user that is not the request maker');
    }
    $update = array_pluck($params, 'username', 'password');
    $user->update(Config::getDb(), $update);
    return true;
  }

  public function b3CheckAuth(array $params)
  {
    $this->checkAuth($params['auth_token'] ?? null);
    return true;
  }

  public function login(array $params)
  {
    $params = array_pluck($params, 'username', 'password');
    $login = SecurityService::getInstance()->attemptLogin($params);
    if ($login === null) {
      throw new RpcException(1001, 'Unauthenticated',
        'The authentication provided was not recognized.');
    }
    return array_pluck($login, 'token');
  }
}
