<?php declare(strict_types=1);
namespace PN\B3\Services;
use PN\B3\{Config, Db};
use PN\B3\Http\{Cookie, Request, Response, Session};
use PN\B3\Core\User;
use PN\B3\Util\Singleton;

class SecurityService
{
  use Singleton;

  // one year's worth of seconds
  protected const TOKEN_VALIDITY_PERIOD = 365 * 24 * 60 * 60;

  protected static function getHmacKey(): string
  {
    $hmacKey = Config::get('hmac_key');
    if ($hmacKey === null) {
      $hmacKey = random_bytes(32);
      Config::set('hmac_key', bin2hex($hmacKey));
    } else {
      $hmacKey = hex2bin($hmacKey);
    }
    return $hmacKey;
  }

  protected function generateSessionToken(User $user): string
  {
    $lookup = random_bytes(32);
    $secret = random_bytes(32);

    $hmacKey = static::getHmacKey();
    $hmac = hash_hmac('sha256', $secret, $hmacKey);
    $now = time();
    Config::getDb()->execute(
      'insert into login_tokens ' .
        '( lookup,  secret,  user_id,  created_at,  valid_until) values ' .
        '(:lookup, :secret, :user_id, :created_at, :valid_until)',
      [
        ':lookup' => bin2hex($lookup),
        ':secret' => $hmac,
        ':user_id' => $user->id,
        ':created_at' => $now,
        ':valid_until' => $now + static::TOKEN_VALIDITY_PERIOD,
      ]);

    return bin2hex($lookup . $secret);
  }

  public function verifySessionToken(string $token): ?User
  {
    if (strlen($token) !== 128) {
      return false;
    }

    $lookup = substr($token, 0, 64);
    $secret = substr($token, 64, 64);

    $token = Config::getDb()->selectOne(
      'select user_id, secret, valid_until from login_tokens ' .
        'where lookup = :lookup',
      [':lookup' => $lookup]);
    if ($token === null) {
      return null;
    }
    if ($token['valid_until'] !== null && $token['valid_until'] < time()) {
      // TODO: GC here or within cron
      return null;
    }

    $hmacKey = static::getHmacKey();
    $hmac = hash_hmac('sha256', hex2bin($secret), $hmacKey);
    if ( ! hash_equals($token['secret'], $hmac)) {
      return null;
    }

    $userId = intval($token['user_id']);
    return User::lookup(Config::getDb(), ['id' => intval($token['user_id'])]);
  }

  public function checkAuthentication(Request $rq): bool
  {
    $token = null;

    if ($rq->headers->has('Authorization')) {
      $auth = $rq->headers['Authorization'];
      $auth = explode(' ', $auth, 2);
      if ($auth[0] === 'Bearer') {
        $token = $auth[1];
      }
    } else if ($rq->cookies->has('st')) {
      $token = $rq->cookies['st'];
    }

    if ($token === null) {
      return false;
    }

    $user = $this->verifySessionToken($token);
    if ($user === null) {
      return false;
    }

    $rq->attributes['auth.token'] = $token;
    $rq->attributes['auth.user'] = $user;
    return true;
  }

  public function attemptLogin(array $parameters): ?array
  {
    $user = User::lookup(Config::getDb(),
      ['username' => $parameters['username']]);
    if ($user === null) {
      return null;
    }

    if ( ! password_verify($parameters['password'], $user->password)) {
      return null;
    }

    if (password_needs_rehash($user->password, PASSWORD_DEFAULT)) {
      $hash = password_hash($parameters['password'], PASSWORD_DEFAULT);
      $user->update(['password' => $hash]);
    }

    $token = $this->generateSessionToken($user);
    $tokenCookie = new Cookie('st', $token);
    $tokenCookie->maxAge = static::TOKEN_VALIDITY_PERIOD;
    return [
      'user' => $user,
      'token' => $token,
      'token_cookie' => $tokenCookie,
    ];
  }

  public function performLogout(Request $rq): Response
  {
    $resp = Response::redirectTo('?login');
    if ($rq->cookies->has('st')) {
      $resp->cookies[] = Cookie::unset('st');
    }
    return $resp;
  }
}
