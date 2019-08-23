<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreAuth;
use PN\B3\Extension;
use PN\B3\Dispatch\{Dispatcher, ControllerHandler};

new class extends Extension {
  const ROUTES = [
    'login' => [AuthController::class, 'loginAction'],
    'logout' => [AuthController::class, 'logoutAction'],
  ];

  public function __construct()
  {
    parent::__construct([
      'id' => 'b3/core-auth',
      'description' => 'Core: Authentication',
      'author' => 'b3 <b3@pn.id.lv>',
    ]);

    $this->addEventListener('b3-ext.boot', function () {
      $dispatcher = Dispatcher::getInstance();
      foreach (static::ROUTES as $name => $target) {
        [$controller, $action] = $target;
        $target = new ControllerHandler($controller, $action);
        $dispatcher->addRoute($name, $target);
      }
    });
  }
};
