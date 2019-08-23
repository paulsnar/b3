<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreCms;
use PN\B3\Ext\CoreCms\Controllers as Ctrl;
use PN\B3\Extension;
use PN\B3\Dispatch\{Dispatcher, ControllerHandler};

new class extends Extension {
  const ROUTES = [
    'home' => [Ctrl\BlogController::class, 'homeAction'],
    'posts' => [Ctrl\BlogController::class, 'postsAction'],
    'posts/new' => [Ctrl\BlogController::class, 'postsNewAction'],
    'posts/edit' => [Ctrl\BlogController::class, 'postsEditAction'],
    'posts/delete' => [Ctrl\BlogController::class, 'postsDeleteAction'],
    'posts/preview' => [Ctrl\BlogController::class, 'postsPreviewAction'],

    'user' => [Ctrl\UserController::class, 'userAction'],

    'settings' => [Ctrl\SettingsController::class, 'settingsAction'],
  ];

  public function __construct()
  {
    parent::__construct([
      'id' => 'b3/core-cms',
      'description' => 'Core: CMS functionality',
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