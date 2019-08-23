<?php declare(strict_types=1);
namespace PN\B3\Dispatch;
use PN\B3\Http\{Request, Response};

class ControllerHandler implements HandlerInterface
{
  protected $controller, $action;

  public function __construct(string $controllerClass, string $action)
  {
    $this->controller = $controllerClass;
    $this->action = $action;
  }

  public function handle(Request $rq): Response
  {
    $ctrl = $this->controller;
    if (is_string($ctrl)) {
      $ctrl = $this->controller = new $ctrl();
    }
    return $ctrl->dispatch($rq, $this->action);
  }
}
