<?php declare(strict_types=1);
namespace PN\B3\Http;

class Cookie
{
  // The actual significance of the timestamp value used is left as an exercise
  // to the reader.
  protected const NETSCAPE_EPOCH = 787507200;

  public
    $name,
    $value,
    $expiresAt,
    $maxAge,
    $domain,
    $path,
    $isHostScoped,
    $isSecure,
    $isHttpOnly;

  public function __construct(string $name, string $value)
  {
    $this->name = $name;
    $this->value = $value;
  }

  public static function unset(string $name)
  {
    $cookie = new static($name, '-');
    $cookie->expiresAt = new \DateTimeImmutable('@' . static::NETSCAPE_EPOCH);
    return $cookie;
  }

  public function send()
  {
    $name = $this->name;
    if ($this->isHostScoped) {
      $name = "__Host-{$name}";
    } else if ($this->isSecure) {
      $name = "__Secure-{$name}";
    }
    $line = "{$this->name}={$this->value}";
    $extra = [ ];

    if ($this->maxAge !== null) {
      $extra[] = 'Max-Age=' . $this->maxAge;
    } else if ($this->expiresAt !== null) {
      $extra[] = 'Expires=' . $this->expiresAt->format(\DateTime::RFC2822);
    }

    if ($this->domain !== null) {
      $extra[] = 'Domain=' . $this->domain;
    }
    if ($this->path !== null && ! $this->isHostScoped) {
      $extra[] = 'Path=' . $this->path;
    }
    if ($this->isSecure || $this->isHostScoped) {
      $extra[] = 'Secure';
    }
    if ($this->isHttpOnly) {
      $extra[] = 'HttpOnly';
    }

    if ($extra !== [ ]) {
      array_unshift($extra, '');
    }
    $line .= implode('; ', $extra);
    $line = 'Set-Cookie: ' . $line;
    header($line, false);
  }
}
