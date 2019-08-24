<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreCms;
use PN\B3\Ext\CoreCms\Controllers as Ctrl;
use PN\B3\Extension;
use PN\B3\Dispatch\{Dispatcher, ControllerHandler};
use const PN\B3\B3_VERSION;

new class extends Extension {
  const ROUTES = [
    'home' => [Ctrl\SiteController::class, 'homeAction'],
    'sites/new' => [Ctrl\SiteController::class, 'sitesNewAction'],
    'sites/edit' => [Ctrl\SiteController::class, 'sitesEditAction'],

    'posts' => [Ctrl\BlogController::class, 'postsAction'],
    'posts/new' => [Ctrl\BlogController::class, 'postsNewAction'],
    'posts/edit' => [Ctrl\BlogController::class, 'postsEditAction'],
    'posts/delete' => [Ctrl\BlogController::class, 'postsDeleteAction'],
    'posts/preview' => [Ctrl\BlogController::class, 'postsPreviewAction'],
    'posts/show' => [Ctrl\BlogController::class, 'postsShowAction'],
    'rebuild' => [Ctrl\BlogController::class, 'rebuildAction'],

    'user' => [Ctrl\UserController::class, 'userAction'],
  ];

  public function __construct()
  {
    parent::__construct([
      'id' => 'b3/core-cms',
      'description' => 'Core: CMS functionality',
      'author' => 'b3 <b3@pn.id.lv>',
      'version' => B3_VERSION,
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
