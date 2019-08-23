<?php declare(strict_types=1);
namespace PN\B3\Http;

class ResponseException extends \Exception implements HttpSerializable
{
  protected $response;

  public function __construct(Response $response)
  {
    $this->response = $response;
  }

  public function serializeHttp(Request $rq): Response
  {
    return $this->response;
  }
}
