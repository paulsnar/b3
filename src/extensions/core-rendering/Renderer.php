<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreRendering;
use PN\B3\App;
use PN\B3\Core\{Post, Site};
use PN\B3\Render\{Context as RenderContext, TemplateRenderer};
use PN\B3\Util\Singleton;
use function PN\B3\{debug_dump, dir_list_files, path_join};

class Renderer
{
  use Singleton;

  protected $templateRenderer;
  protected
    $indexTemplates = [ ];
  protected $themeRoot, $targetRoot;

  public function __construct()
  {
    $this->themeRoot = path_join(App::ROOT, 'theme');
    $this->templateRenderer = new TemplateRenderer($this->themeRoot);
    $this->templateRenderer->registerGlobal('site', function () {
      return Site::getInstance()->toArray();
    });
    $url = function (string $path): string {
      $base = Site::getInstance()->getBaseUrl();
      $base = rtrim($base, '/');
      $path = ltrim($path, '/');
      return $base . '/' . $path;
    };
    $this->templateRenderer->registerFunction('url', $url);

    $this->templateRenderer->registerFunction('dump', function ($item) {
      var_dump($item);
    });

    $this->indexTemplates = dir_list_files(
      path_join($this->themeRoot, 'index'), true);
    $this->targetRoot = path_join(App::ROOT, 'site');
    if ( ! is_dir($this->targetRoot)) {
      mkdir($this->targetRoot);
    }

    $this->installEventHandlers();
  }

  protected function installEventHandlers()
  {
    $app = App::getInstance();

    $renderPostAndIndexes = function (Post $post) {
      $this->buildIndexes();
      $this->buildPost($post);
      $this->deletePhantomPost();
    };

    $app->addEventListener('b3.posts.new', $renderPostAndIndexes);
    $app->addEventListener('b3.posts.edited', $renderPostAndIndexes);

    $app->addEventListener('b3.posts.deleted', function (Post $post) {
      $this->buildIndexes();
      $this->deletePost($post);
    });
  }

  public function buildIndexes()
  {
    $prefixLength = strlen(path_join($this->themeRoot, 'index', ''));
    [$posts, $cursor] = Site::getInstance()->getPosts();

    foreach ($this->indexTemplates as $template) {
      $targetName = substr($template, $prefixLength);
      $targetPath = path_join($this->targetRoot, $targetName);

      if (strpos($targetName, DIRECTORY_SEPARATOR) !== false) {
        $dir = substr(
          $targetName, 0, strrpos($targetName, DIRECTORY_SEPARATOR));
        $dir = path_join($this->targetRoot, $dir);
        if ( ! is_dir($dir)) {
          mkdir($dir, 0777, true);
        }
      }

      $contents = $this->templateRenderer->render(
        'index/' . $targetName, ['posts' => $posts]);
      file_put_contents($targetPath, $contents);
    }
  }

  protected function getPostTargetPath(Post $post): string
  {
    $targetPath = $post->getUrl() . '.html';
    if (DIRECTORY_SEPARATOR !== '/') {
      $targetPath = str_replace('/', DIRECTORY_SEPARATOR, $targetPath);
    }
    return $targetPath;
  }

  public function buildPost(Post $post)
  {
    $targetPath = $this->getPostTargetPath($post);

    if ($post->contentRendered === null) {
      $post->contentRendered =
        RenderContext::contentRenderer($post->contentType)
          ->render($post->content);
    }


    if (strpos($targetPath, DIRECTORY_SEPARATOR) !== false) {
      $dir = substr($targetPath, 0, strrpos($targetPath, DIRECTORY_SEPARATOR));
      $dir = path_join($this->targetRoot, $dir);
      if ( ! is_dir($dir)) {
        mkdir($dir, 0777, true);
      }
    }

    file_put_contents(
      path_join($this->targetRoot, $targetPath),
      $this->renderPost($post));
  }

  public function buildPhantomPost(Post $post): string
  {
    $targetPath = path_join($this->targetRoot, '_preview.html');

    if ($post->contentRendered === null) {
      $post->contentRendered =
        RenderContext::contentRenderer($post->contentType)
          ->render($post->content);
    }

    file_put_contents($targetPath, $this->renderPost($post));
    return '_preview.html';
  }

  public function renderPost(Post $post)
  {
    // TODO: don't hardcode template name
    // also allow for more flexible archiving, such as by week/month?
    return $this->templateRenderer->render(
      'archive/post.html', compact('post'));
  }

  public function deletePhantomPost()
  {
    $phantom = path_join($this->targetRoot, '_preview.html');
    if (file_exists($phantom)) {
      unlink($phantom);
    }
  }

  public function deletePost(Post $post)
  {
    $targetPath = $this->getPostTargetPath($post);
    if (file_exists($targetPath)) {
      unlink($targetPath);
    }
    // TODO: clean up directory tree if empty
  }
}
