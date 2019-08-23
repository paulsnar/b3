<?php declare(strict_types=1);
namespace PN\B3\Services;
use PN\B3\Http\{Request, Session};
use PN\B3\Util\Singleton;

class CsrfService
{
  use Singleton;

  protected function generateToken(): string
  {
    $token = bin2hex(random_bytes(16));
    Session::set('csrf.token', $token);
    return $token;
  }

  public function getToken()
  {
    $token = Session::get('csrf.token');
    if ($token === null) {
      $token = $this->generateToken();
    }
    return $token;
  }

  public function checkToken(string $token, bool $invalidate = true): bool
  {
    $currentToken = $this->getToken();
    $isValid = hash_equals($currentToken, $token);
    if ($isValid && $invalidate) {
      Session::unset('csrf.token');
    }
    return $isValid;
  }
}
