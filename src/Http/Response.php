<?php declare(strict_types=1);
namespace PN\B3\Http;

class Response
{
  public
    $status,
    $headers,
    $cookies = [ ],
    $body;

  public function __construct(
    int $status = Status::NO_CONTENT,
    array $headers = [ ],
    ?string $body = null
  ) {
    $this->status = $status;
    $this->headers = new HeaderBag($headers);
    $this->body = $body;
  }

  public static function redirectTo(
    string $target,
    int $status = Status::FOUND,
    array $headers = [ ],
    ?string $body = null
  ): self {
    $response = new static($status, $headers, $body);
    $response->headers['Location'] = $target;

    if ($response->body === null) {
      $url = htmlspecialchars($target, ENT_QUOTES | ENT_HTML5);
      $response->body = <<<HTML
<!DOCTYPE HTML>
<article>You are being <a href="{$url}">redirected.</a></article>

HTML;
      $response->headers['Content-Type'] = 'text/html; charset=UTF-8';
    }

    return $response;
  }

  public static function withHtml(
    string $body,
    int $status = Status::OK,
    array $headers = [ ]
  ): self {
    $response = new static($status, $headers, $body);
    $response->headers['Content-Type'] = 'text/html; charset=UTF-8';
    return $response;
  }

  public static function withText(
    string $body,
    int $status = Status::OK,
    array $headers = [ ]
  ): self {
    $response = new static($status, $headers, $body);
    $response->headers['Content-Type'] = 'text/plain; charset=UTF-8';
    return $response;
  }

  public static function withJson(
    $body,
    int $status = Status::OK,
    array $headers = [ ]
  ): self {
    $response = new static($status, $headers, null);
    $response->headers['Content-Type'] = 'application/json; charset=UTF-8';
    $response->body = json_encode($body, JSON_UNESCAPED_SLASHES);
    if ($response->body === null) {
      throw new \RuntimeException(
        'JSON encode failed: ' . json_last_error_msg());
    }
    $response->body .= "\n";
    return $response;
  }

  public function send()
  {
    http_response_code($this->status);

    foreach ($this->headers as $key => $value) {
      header("{$key}: {$value}");
    }

    // if no Content-Type is provided, remove the dumb PHP default of text/html
    if ( ! $this->headers->offsetExists('Content-Type')) {
      header('Content-Type:');
      header_remove('Content-Type');
    }

    foreach ($this->cookies as $cookie) {
      $cookie->send();
    }

    if ($this->body !== null) {
      if ( ! $this->headers->has('Content-Length')) {
        header('content-length: ' . strlen($this->body));
      }
      echo $this->body;
    }
  }
}
