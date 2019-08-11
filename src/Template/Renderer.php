<?php declare(strict_types=1);
namespace PN\B3\Template;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Renderer implements RendererInterface
{
  protected $environment, $templateCache = [ ];

  public function __construct(string $templateRoot)
  {
    $loader = new FilesystemLoader($templateRoot);
    $this->environment = new Environment($loader, [ ]);
  }

  public function render(string $templateName, array $context = [ ]): string
  {
    if (array_key_exists($templateName, $this->templateCache)) {
      $template = $this->templateCache[$templateName];
    } else {
      $template = $this->environment->load($templateName);
    }

    return $template->render($context);
  }
}
