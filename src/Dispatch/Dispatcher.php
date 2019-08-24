<?php declare(strict_types=1);
namespace PN\B3\Dispatch;
use PN\B3\Events\EventTarget;
use PN\B3\Http\{HttpSerializable, Request, Response, Status};
use PN\B3\Rpc;
use PN\B3\Util\Singleton;

class Dispatcher extends EventTarget
{
  use Singleton;

  protected $handlers = [ ];
  protected $currentRequest;

  public function __construct()
  {
    parent::__construct();

    // TODO: there is probably a better way to do this
    $this->handlers['rpc'] = new ObjectHandler(Rpc::class, 'rpcAction');
  }

  public function addRoute(string $action, HandlerInterface $handler)
  {
    $this->handlers[$action] = $handler;
  }

  public function getCurrentRequest(): ?Request
  {
    return $this->currentRequest;
  }

  public function dispatch()
  {
    $request = $this->currentRequest = Request::fromGlobals();
    $this->dispatchEvent('request', $request);

    $action = $request->action ?: 'home';

    $handler = $this->handlers[$action] ?? null;
    if ($handler === null) {
      return Response::withText(
        "Sorry, not found. ({$action})\n", Status::NOT_FOUND);
    }

    try {
      $response = $handler->handle($request);
    } catch (HttpSerializable $exc) {
      $response = $exc->serializeHttp($request);
    }
    $this->dispatchEvent('response', $response, $request);

    $this->currentRequest = null;
    return $response;
  }
}
