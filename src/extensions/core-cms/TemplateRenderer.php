<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreCms;
use PN\B3\Http\{Response, Session, Status};
use PN\B3\Render\TemplateRenderer as B3TemplateRenderer;
use PN\B3\Services\CsrfService;
use PN\B3\Util\Singleton;
use function PN\B3\path_join;

class TemplateRenderer
{
  use Singleton;

  public static function render(string $name, array $context = [ ]): string
  {
    return static::getInstance()->renderTemplate($name, $context);
  }

  public static function renderResponse(
    string $name,
    array $context = [ ],
    int $status = Status::OK,
    array $headers = [ ]
  ): Response {
    $content = static::render($name, $context);
    return Response::withHtml($content, $status, $headers);
  }

  protected $renderer;

  public function __construct()
  {
    $this->renderer = new B3TemplateRenderer(path_join(__DIR__, 'templates'));

    $this->renderer->registerFunction('csrf', function (): string {
      return CsrfService::getInstance()->getToken();
    });

    $this->renderer->registerFunction('flash', function (string $key) {
      return Session::getFlash($key);
    });
  }

  public function renderTemplate(string $name, array $context = [ ]): string
  {
    return $this->renderer->render($name, $context);
  }
}
