<?php declare(strict_types=1);
namespace PN\B3;
use PN\B3\Events\EventTarget;
use PN\B3\Http\{Request, Response, Status};
use PN\B3\Rpc\RpcException;
use function PN\B3\str_starts_with;

class Rpc extends EventTarget
{
  use Util\Singleton;

  protected $handlers = [ ];

  public function __construct()
  {
    parent::__construct();

    $this->addEventListener('b3.singletonboot', function () {
      App::getInstance()->dispatchEvent('b3.rpcinstall');
    });
  }

  public function installHandler(string $method, callable $handler)
  {
    $this->handlers[$method] = \Closure::fromCallable($handler);
  }

  protected static function jsonResponse(array $response): Response
  {
    $status = Status::OK;
    if (array_key_exists('jsonrpc', $response)) {
      if (array_key_exists('error', $response)) {
        $error = $response['error'];
        $code = $error['code'];
        if ($code === -32601) {
          $status = Status::NOT_FOUND;
        } else if ($code === -32603 || (-32100 < $code && $code <= -32000)) {
          $status = Status::INTERNAL_SERVER_ERROR;
        } else {
          $status = Status::BAD_REQUEST;
        }
      }
    }

    return Response::withJson($response, $status);
  }

  protected static function errorResponse(
    int $code,
    string $message,
    $data = null
  ): Response {
    $error = compact('code', 'message');
    if ($data !== null) {
      $error['data'] = $data;
    }
    return static::jsonResponse([
      'jsonrpc' => '2.0',
      'id' => null,
      'error' => $error,
    ]);
  }

  const NEEDS_AUTHENTICATION = false;

  public function rpcAction(Request $rq): Response
  {
    if ($rq->method !== 'POST') {
      return static::errorResponse(-32600, 'Invalid Request',
        'JSON-RPC server accepts POST requests only.');
    }

    $contentType = $rq->headers->get('Content-Type', '');
    $contentType = explode(';', $contentType);
    $contentTypeExtra = trim($contentType[1] ?? '');
    $contentType = trim($contentType[0]);
    if ($contentType !== 'application/json') {
      return static::errorResponse(-32600, 'Invalid Request',
        'The incoming message must have Content-Type of application/json.');
    }
    if (str_starts_with($contentTypeExtra, 'charset=')) {
      $charset = substr($contentTypeExtra, 8);
      $charset = strtoupper($charset);
      if ($charset !== 'UTF-8') {
        return static::errorResponse(-32600, 'Invalid Request',
          'The incoming message must have Encoding of UTF-8.');
      }
    }

    $request = json_decode($rq->body, true);
    if ($request === null) {
      if (json_last_error() !== JSON_ERROR_NONE) {
        return static::errorResponse(-32700, 'Parse error',
          'The incoming message could not be parsed as valid JSON.');
      }
    }

    if (is_array($request) && array_key_exists(0, $request)) {
      $response = array_map([$this, 'handleRpcCall'], $request);
      $response = array_filter($response, function ($item) {
        return $item !== null;
      });
    } else {
      $response = $this->handleRpcCall($request);
    }

    if ($response === null) {
      return new Response(Status::NO_CONTENT);
    } else {
      return static::jsonResponse($response);
    }
  }

  protected function handleRpcCall($request): ?array
  {
    if ( ! is_array($request) ||
        ($request['jsonrpc'] ?? null) !== '2.0' ||
         ! array_key_exists('method', $request)) {
      return ['jsonrpc' => '2.0', 'id' => null, 'error' => [
        'code' => -32600, 'message' => 'Invalid Request',
        'data' => 'The incoming message does not comprise ' .
          'a valid Request object.']];
    }

    $method = $request['method'];
    $params = $request['params'] ?? [ ];

    try {
      $error = null;
      $result = $this->call($method, $params);
    } catch (RpcException $err) {
      $error = $err->toArray();
    } catch (\Throwable $err) {
      $dataLine = get_class($err);
      if (($message = $err->getMessage()) !== '') {
        $dataLine .= ': ' . $message;
      }
      $error = [
        'code' => -30000,
        'message' => 'Execution error',
        'data' =>
          'While running the method, an exception was caught: ' . $dataLine,
      ];
    }

    if (array_key_exists('id', $request)) {
      $response = ['jsonrpc' => '2.0', 'id' => $request['id']];
      if ($error !== null) {
        $response['error'] = $error;
      } else {
        $response['result'] = $result;
      }
      return $response;
    } else {
      return null;
    }
  }

  public function call(string $method, array $params)
  {
    $handler = $this->handlers[$method] ?? null;
    if ($handler === null) {
      throw new RpcException(-32601, 'Method not found',
        "The server does not implement this method: {$method}");
    }

    return $handler($method, $params);
  }

  public function handleDemoSayHello(string $method, array $params)
  {
    $name = 'world';
    if (array_key_exists('name', $params) &&
        is_string($params['name'])) {
      $name = $params['name'];
    }
    return "Hello, {$name}!";
  }
}
