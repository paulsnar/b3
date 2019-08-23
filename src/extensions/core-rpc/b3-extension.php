<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreRpc;
use PN\B3\{Extension, Rpc};
use PN\B3\Rpc\RpcException;
use PN\B3\Ext\CoreRpc\Handlers as Hdl;

new class extends Extension {
  const METHOD_MAPPINGS = [
    'b3.getPost' => Hdl\PostsHandler::class,
    'b3.listPosts' => Hdl\PostsHandler::class,
    'b3.newPost' => Hdl\PostsHandler::class,
    'b3.editPost' => Hdl\PostsHandler::class,
    'b3.deletePost' => Hdl\PostsHandler::class,

    'b3.checkAuth' => Hdl\UsersHandler::class,
    'b3.login' => Hdl\UsersHandler::class,
    'b3.updateUser' => Hdl\UsersHandler::class,

    'b3.getSettings' => Hdl\SettingsHandler::class,
    'b3.updateSettings' => Hdl\SettingsHandler::class,

  ];

  public function __construct()
  {
    parent::__construct([
      'id' => 'b3/core-rpc',
      'description' => 'Core: RPC handlers',
      'author' => 'b3 <b3@pn.id.lv>',
    ]);

    $this->addGlobalEventListener('b3.rpcinstall', function () {
      $rpc = Rpc::getInstance();

      foreach (static::METHOD_MAPPINGS as $method => $handler) {
        $rpc->installHandler($method, [$handler, 'handleCall']);
      }
    });
  }

  public function handleDemoAdd(array $params)
  {
    $items = $params['items'] ?? null;
    if ( ! is_array($items)) {
      $items = null;
    }

    if ($items === null) {
      throw new RpcException(
        1000, 'Invalid parameter: `items` must be an array of numbers');
    }

    return array_reduce($items, function ($carry, $item) {
      if ( ! is_numeric($item)) {
        throw new RpcException(
          1000, 'Invalid parameter: `items` must be an array of numbers');
      }

      return $carry + $item;
    }, 0);
  }
};
