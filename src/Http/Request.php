<?php declare(strict_types=1);
namespace PN\B3\Http;
use PN\B3\Util\Bag;

class Request
{
  public
    $method,
    $action,
    $headers,
    $query,
    $form,
    $files,
    $cookies,
    $body,
    $attributes;

  public function __construct()
  {
    $this->headers = new HeaderBag();
    $this->query = new Bag();
    $this->form = new Bag();
    $this->files = new Bag();
    $this->cookies = new Bag();
    $this->attributes = new Bag();
  }

  public static function fromGlobals(): self
  {
    $rq = new static();

    $rq->method = $_SERVER['REQUEST_METHOD'];
    $rq->headers = HeaderBag::fromGlobals();

    $queryStr = $_SERVER['QUERY_STRING'] ?? '';

    $path = $_SERVER['REQUEST_URI'];
    $pathQueryStart = strpos($path, '?');
    if ($queryStr === '' && $pathQueryStart !== false) {
      $queryStr = substr($path, $pathQueryStart + 1);
    }

    $queryParts = explode('&', $queryStr);
    if ($queryParts !== [ ]) {
      $rq->action = $queryParts[0];
    }

    $query = [ ];
    parse_str($queryStr, $query);
    $rq->query = new Bag($query);
    $rq->form = new Bag($_POST);
    $rq->files = new Bag($_FILES);
    $rq->cookies = new Bag($_COOKIE);

    if ($rq->method !== 'HEAD' &&
        $rq->method !== 'GET') {
      $rq->body = file_get_contents('php://input');
    }

    return $rq;
  }
}
