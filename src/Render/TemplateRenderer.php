<?php declare(strict_types=1);
namespace PN\B3\Render;
use PN\B3\App;
use PN\B3\Http\{Response, Session, Status};
use PN\B3\Services\CsrfService;
use Twig\{Environment as TwigEnvironment, TwigFilter, TwigFunction};
use Twig\Loader\FilesystemLoader;
use function PN\B3\path_join;

class TemplateRenderer
{
  protected const SYSTEM_TEMPLATE_ROOT =
    App::PRIVATE_ROOT . DIRECTORY_SEPARATOR . '/templates';

  protected $loader, $environment;

  public function __construct(string $root)
  {
    $this->loader = new FilesystemLoader();
    $this->loader->addPath(static::SYSTEM_TEMPLATE_ROOT, 'system');
    $this->loader->addPath($root);

    $this->environment = new TwigEnvironment($this->loader, [ /* TODO */ ]);
  }

  public function registerFunction(string $name, callable $callback)
  {
    $this->environment->addFunction(new TwigFunction($name, $callback));
  }

  public function render(string $name, array $context = [ ]): string
  {
    $template = $this->environment->load($name);
    return $template->render($context);
  }
}
