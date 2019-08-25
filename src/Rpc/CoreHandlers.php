<?php declare(strict_types=1);
namespace PN\B3\Rpc;
use PN\B3\Rpc;
use PN\B3\Rpc\CoreHandlers\{
    PostsHandler,
    SitesHandler,
    TemplatesHandler,
    UsersHandler};

class CoreHandlers
{
  const METHOD_MAP = [
    'b3.listSites' => [SitesHandler::class, 'listSites'],
    'b3.getSite' => [SitesHandler::class, 'getSite'],
    'b3.newSite' => [SitesHandler::class, 'newSite'],
    'b3.editSite' => [SitesHandler::class, 'editSite'],
    'b3.deleteSite' => [SitesHandler::class, 'deleteSite'],

    'b3.listPosts' => [PostsHandler::class, 'listPosts'],
    'b3.getPost' => [PostsHandler::class, 'getPost'],
    'b3.newPost' => [PostsHandler::class, 'newPost'],
    'b3.editPost' => [PostsHandler::class, 'editPost'],
    'b3.deletePost' => [PostsHandler::class, 'deletePost'],
    'b3.rebuild' => [PostsHandler::class, 'rebuild'],

    'b3.checkAuth' => [UsersHandler::class, 'checkAuth'],
    'b3.login' => [UsersHandler::class, 'login'],
    'b3.getUser' => [UsersHandler::class, 'getUser'],
    'b3.updateUser' => [UsersHandler::class, 'updateUser'],

    'b3.listTemplates' => [TemplatesHandler::class, 'listTemplates'],
    'b3.getTemplate' => [TemplatesHandler::class, 'getTemplate'],
    'b3.newTemplate' => [TemplatesHandler::class, 'newTemplate'],
    'b3.editTemplate' => [TemplatesHandler::class, 'editTemplate'],
    'b3.deleteTemplate' => [TemplatesHandler::class, 'deleteTemplate'],
  ];

  public static function install()
  {
    $rpc = Rpc::getInstance();

    foreach (static::METHOD_MAP as $method => $handler) {
      $rpc->installHandler($method, $handler);
    }
  }
}
