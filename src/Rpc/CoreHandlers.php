<?php declare(strict_types=1);
namespace PN\B3\Rpc;
use PN\B3\Rpc;
use PN\B3\Rpc\CoreHandlers\{
    PostsHandler,
    UsersHandler,
    SettingsHandler};

class CoreHandlers
{
  const METHOD_MAP = [
    'b3.getPost' => [PostsHandler::class, 'getPost'],
    'b3.listPosts' => [PostsHandler::class, 'listPosts'],
    'b3.newPost' => [PostsHandler::class, 'newPost'],
    'b3.editPost' => [PostsHandler::class, 'editPost'],
    'b3.deletePost' => [PostsHandler::class, 'deletePost'],
    'b3.rebuild' => [PostsHandler::class, 'rebuild'],

    'b3.checkAuth' => [UsersHandler::class, 'checkAuth'],
    'b3.login' => [UsersHandler::class, 'login'],
    'b3.getUser' => [UsersHandler::class, 'getUser'],
    'b3.updateUser' => [UsersHandler::class, 'updateUser'],

    'b3.getSettings' => [SettingsHandler::class, 'getSettings'],
    'b3.updateSettings' => [SettingsHandler::class, 'updateSettings'],
  ];

  public static function install()
  {
    $rpc = Rpc::getInstance();

    foreach (static::METHOD_MAP as $method => $handler) {
      $rpc->installHandler($method, $handler);
    }
  }
}
