<?php declare(strict_types=1);
namespace PN\B3\Http\Middleware;
use PN\B3\App;
use PN\B3\Events\EventTarget;
use PN\B3\Util\Singleton;

class Registry extends EventTarget
{
  use Singleton;

  public function __construct()
  {
    parent::__construct();

    $this->addEventListener('b3.singletonboot', function () {
      App::getInstance()->dispatchEvent('b3.middlewareinstall');
    });
  }

  protected $middleware = [ ];

  public function register(BaseMiddleware $mw)
  {
    $this->middleware[$mw->name] = $mw;
  }

  public function lookup(string $name): BaseMiddleware
  {
    $middleware = $this->middleware[$name] ?? null;
    if ($middleware === null) {
      throw new \RuntimeException("Unknown middleware: {$name}");
    }
    return $middleware;
  }
}
