<?php declare(strict_types=1);
namespace PN\Blog\Markdown;
use League\CommonMark\{DocParser, Environment, HtmlRenderer};
use League\CommonMark\Ext\SmartPunct\SmartPunctExtension;

class Renderer implements RendererInterface
{
  protected $env, $parser, $renderer;
  public function __construct()
  {
    $this->env = Environment::createCommonMarkEnvironment();
    $this->env->addExtension(new Extension());
    $this->env->addExtension(new SmartPunctExtension());

    $this->parser = new DocParser($this->env);
    $this->renderer = new HtmlRenderer($this->env);
  }

  public function render(string $markdown): string
  {
    return $this->renderer->renderBlock($this->parser->parse($markdown));
  }
}

