<?php declare(strict_types=1);
namespace PN\B3\Template;
use Twig\{Environment, TwigFilter, TwigFunction};
use Twig\Loader\FilesystemLoader;
use function PN\B3\url_absolute;

class Renderer implements RendererInterface
{
  protected $loader, $environment, $templateCache = [ ];

  public function __construct(string $templateRoot)
  {
    $this->loader = new FilesystemLoader($templateRoot);
    $this->environment = new Environment($this->loader, [ ]);
    $this->extend($this->environment);
  }

  protected function extend(Environment $env)
  {
    $date_humanreadable = function (\DateTimeInterface $datetime): string {
      return $datetime->format('F j, Y');
    };
    $env->addFilter(new TwigFilter('date_humanreadable', $date_humanreadable));

    $url = function (string $path): string {
      return url_absolute($path);
    };
    $env->addFunction(new TwigFunction('url', $url));
  }

  public function render(string $templateName, array $context = [ ]): string
  {
    if (array_key_exists($templateName, $this->templateCache)) {
      $template = $this->templateCache[$templateName];
    } else {
      if ( ! $this->loader->exists($templateName)) {
        throw new TemplateNotFoundException($templateName);
      }
      $template = $this->environment->load($templateName);
    }

    return $template->render($context);
  }
}
