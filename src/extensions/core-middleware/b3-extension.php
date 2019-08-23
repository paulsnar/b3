<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreMiddleware;
use PN\B3\Extension;
use PN\B3\Http\Middleware\Registry;

new class extends Extension {
  const MIDDLEWARE = [
    'b3.auth' => AuthMiddleware::class,
    'b3.csrf' => CsrfMiddleware::class,
  ];

  public function __construct()
  {
    parent::__construct([
      'id' => 'b3/core-middleware',
      'description' => 'Core: Middleware',
      'author' => 'b3 <b3@pn.id.lv>',
    ]);

    $this->addGlobalEventListener('b3.middlewareinstall', function () {
      $registry = Registry::getInstance();
      foreach (static::MIDDLEWARE as $class) {
        $middleware = new $class();
        $registry->register($middleware);
      }
    });
  }
};
