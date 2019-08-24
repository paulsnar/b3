<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreRendering;
use PN\B3\{App, Rpc};
use PN\B3\Core\{Post, Site};
use PN\B3\Render\{Context as RenderContext, TemplateRenderer};
use PN\B3\Templating\{TemplateLoader, Template};
use PN\B3\Util\Singleton;
use Twig\Environment as TwigEnvironment;
use function PN\B3\{dir_list_files, path_join};

class Renderer
{
  use Singleton;

  protected $siteEnvironments = [ ], $siteLoaders = [ ];

  public function __construct()
  {
    $this->installEventHandlers();
  }

  protected function installEventHandlers()
  {
    $app = App::getInstance();

    $renderPostAndIndexes = function (Site $site, Post $post) {
      $this->buildIndexes($site);
      $this->buildPost($site, $post);
      $this->deletePhantomPost($site);
    };

    $app->addEventListener('b3.posts.new', $renderPostAndIndexes);
    $app->addEventListener('b3.posts.edited', $renderPostAndIndexes);

    $renderIndexes = function (Site $site, Post $post) {
      $this->buildIndexes($site);
    };
    $app->addEventListener('b3.posts.deleted', $renderIndexes);
  }

  protected function getSiteEnvironment(Site $site): TwigEnvironment
  {
    if (array_key_exists($site->id, $this->siteEnvironments)) {
      return $this->siteEnvironments[$site->id];
    }

    $loader = new TemplateLoader($site);
    $this->siteLoaders[$site->id] = $loader;
    $environment = new TwigEnvironment($loader);
    $this->siteEnvironments[$site->id] = $environment;

    $environment->addGlobal('site', $site);

    return $environment;
  }

  public function buildIndexes(Site $site)
  {
    $env = $this->getSiteEnvironment($site);
    $loader = $this->siteLoaders[$site->id];

    $posts = Post::selectAll([
      'state' => Post::STATE_PUBLISHED,
      'site_id' => $site->id,
    ]);

    foreach ($loader->getAllTemplates(Template::TYPE_INDEX) as $template) {
      $twigTemplate = $env->load($template->name);
      $contents = $twigTemplate->render(['posts' => $posts]);

      $directory = $template->getTargetDirectory();
      if ( ! is_dir($directory)) {
        mkdir($directory);
      }
      file_put_contents($template->getTargetPath(), $contents);
    }
  }

  protected function getPostTargetPath(Site $site, Post $post): string
  {
    $targetPath = $post->url . '.html';
    if (DIRECTORY_SEPARATOR !== '/') {
      $targetPath = str_replace('/', DIRECTORY_SEPARATOR, $targetPath);
    }
    return path_join($site->target_path, $targetPath);
  }

  public function buildPost(Site $site, Post $post)
  {
    $targetPath = $this->getPostTargetPath($site, $post);
    $content = $this->renderPost($site, $post);

    if (strpos($targetPath, DIRECTORY_SEPARATOR) !== false) {
      $dir = substr($targetPath, 0, strrpos($targetPath, DIRECTORY_SEPARATOR));
      if ( ! is_dir($dir)) {
        mkdir($dir, 0777, true);
      }
    }

    file_put_contents($targetPath, $content);
  }

  public function buildPhantomPost(Site $site, Post $post): string
  {
    $targetPath = path_join($site->target_path, '_preview.html');

    file_put_contents($targetPath, $this->renderPost($site, $post));
    return '_preview.html';
  }

  public function renderPost(Site $site, Post $post)
  {
    if ($post->body === null) {
      $post->body = RenderContext::contentRenderer($post->content_type)
          ->render($post->content);
    }

    $env = $this->getSiteEnvironment($site);
    $loader = $this->siteLoaders[$site->id];

    $template = $loader->getAllTemplates('entry');
    if ($template === [ ]) {
      throw new \RuntimeException(
        "No entry template defined for site {$site->id}");
    }
    $template = $template[0];

    $twigTemplate = $env->load($template->name);
    return $twigTemplate->render(['post' => $post]);
  }

  public function deletePhantomPost(Site $site)
  {
    $phantom = path_join($site->target_path, '_preview.html');
    if (file_exists($phantom)) {
      unlink($phantom);
    }
  }

  public function deletePost(Site $site, Post $post)
  {
    $targetPath = $this->getPostTargetPath($site, $post);
    if (file_exists($targetPath)) {
      unlink($targetPath);
    }
    // TODO: clean up directory tree if empty
  }
}
