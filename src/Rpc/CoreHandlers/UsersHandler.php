<?php declare(strict_types=1);
namespace PN\B3\Rpc\CoreHandlers;
use PN\B3\Config;
use PN\B3\Core\User;
use PN\B3\Rpc\{OptionalAuthenticationAware, RpcException};
use PN\B3\Services\SecurityService;
use function PN\B3\{array_pluck, debug_print};

class UsersHandler implements OptionalAuthenticationAware
{
  public function callNeedsAuthentication(
    string $method,
    string $objMethod,
    array $params
  ): bool {
    return $method !== 'b3.login';
  }

  public function getUser(array $params, User $user): array
  {
    $db = Config::getDb();
    if (array_key_exists('id', $params)) {
      $user = $db->selectOne(
        'select id, username from users where id = :id',
        [':id' => $params['id']]);
    } else if (array_key_exists('username', $params)) {
      $user = $db->selectOne(
        'select id, username from users where username = :username',
        [':username' => $params['username']]);
    } else {
      throw RpcException::invalidParams(
        'No valid user lookup criterion was provided.');
    }
    if ($user === null) {
      throw new RpcException(1003, 'Item not found',
        'The criteria provided do not match any object.');
    }
    return $user;
  }

  public function updateUser(array $params, User $user): bool
  {
    if ($params['user_id'] !== $user->id) {
      throw new RpcException(1002, 'Unauthorized',
        'Cannot update attributes for user that is not making the request.');
    }

    $update = array_pluck($params, 'username', 'password');
    $user->update(Config::getDb(), $update);
    return true;
  }

  public function checkAuth(array $params, User $user): bool
  {
    return true;
  }

  public function login(array $params, ?User $user): array
  {
    $params = array_pluck($params, 'username', 'password');
    $login = SecurityService::getInstance()->attemptLogin($params);
    if ($login === null) {
      throw new RpcException(1001, 'Unauthenticated',
        'The authentication provided was either incomplete or invalid.');
    }
    return array_pluck($login, 'token');
  }
}
