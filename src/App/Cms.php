<?php declare(strict_types=1);
namespace PN\B3\App;
use PN\B3\{Controllers as Ctrl, Rpc};
use PN\B3\Http\Session;
use PN\B3\Render\TemplateRenderer;
use PN\B3\Services\CsrfService;
use PN\B3\Util\Singleton;
use function PN\B3\path_join;

class Cms
{
  use Singleton;

  public $templateRenderer;

  public function __construct()
  {
    $templatePath = path_join(dirname(dirname(__DIR__)), 'templates');
    $this->templateRenderer = new TemplateRenderer($templatePath);

    $this->templateRenderer->registerFunction('csrf', function (): string {
      return CsrfService::getInstance()->getToken();
    });

    $this->templateRenderer->registerFunction('flash', function (string $key) {
      return Session::getFlash($key);
    });
  }

  public function getDispatchTable()
  {
    return [
      'login' => [Ctrl\SecurityController::class, 'loginAction'],
      'logout' => [Ctrl\SecurityController::class, 'logoutAction'],

      'rpc' => [Rpc::class, 'rpcAction'],

      'home' => [Ctrl\BlogController::class, 'homeAction'],
      'posts' => [Ctrl\BlogController::class, 'postsAction'],
      'posts/new' => [Ctrl\BlogController::class, 'postsNewAction'],
      'posts/edit' => [Ctrl\BlogController::class, 'postsEditAction'],
      'posts/delete' => [Ctrl\BlogController::class, 'postsDeleteAction'],
      'posts/preview' => [Ctrl\BlogController::class, 'postsPreviewAction'],

      'install' => [Ctrl\ConfigController::class, 'installAction'],
      'settings' => [Ctrl\ConfigController::class, 'settingsAction'],

      'user' => [Ctrl\UserController::class, 'userAction'],
    ];
  }
}
