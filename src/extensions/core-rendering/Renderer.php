<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreRendering;
use PN\B3\{App, Rpc};
use PN\B3\Core\{Post, Site};
use PN\B3\Render\{Context as RenderContext, TemplateRenderer};
use PN\B3\Templating\{TemplateLoader, Template};
use PN\B3\Util\Singleton;
use Twig\{Environment as TwigEnvironment, TwigFunction};
use function PN\B3\{dir_list_files, file_write, path_join, url_join};

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

    $renderTemplateIfIndex = function (Template $template) {
      if ($template->type === Template::TYPE_INDEX) {
        $site = Site::lookup(['id' => $template->site_id]);
        if ($site === null) {
          return;
        }
        $posts = Post::selectAll([
          'state' => Post::STATE_PUBLISHED,
          'site_id' => $site->id,
        ]);
        $this->buildIndexTemplate($site, $template, $posts);
      }
    };
    $app->addEventListener('b3.templates.new', $renderTemplateIfIndex);
    $app->addEventListener('b3.templates.edited', $renderTemplateIfIndex);
  }

  // Adapted from https://daringfireball.net/projects/mt-escapeforjson/
  private const JSON_ESCAPABLES = [
    // 0x00..0x1F
    "\00" => '\\u0000', "\01" => '\\u0001', "\02" => '\\u0002',
    "\03" => '\\u0003', "\04" => '\\u0004', "\05" => '\\u0005',
    "\06" => '\\u0006', "\07" => '\\u0007', "\10" => '\\b',
    "\11" => '\\t',     "\12" => '\\n',     "\13" => '\\u000b',
    "\14" => '\\f',     "\15" => '\\r',     "\16" => '\\u000e',
    "\17" => '\\u000f', "\20" => '\\u0010', "\21" => '\\u0011',
    "\22" => '\\u0012', "\23" => '\\u0013', "\24" => '\\u0014',
    "\25" => '\\u0015', "\26" => '\\u0016', "\27" => '\\u0017',
    "\30" => '\\u0018', "\31" => '\\u0019', "\32" => '\\u001a',
    "\33" => '\\u001b', "\34" => '\\u001c', "\35" => '\\u001d',
    "\36" => '\\u001e', "\37" => '\\u001f',

    '"' => '\\"', '\\' => '\\\\',
  ];

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

    $url = function (string $path) use ($site): string {
      return url_join($site->base_url, $path);
    };
    $environment->addFunction(new TwigFunction('url', $url));

    $escaperExt = $environment->getExtension(
      \Twig\Extension\EscaperExtension::class);
    $escapeXml = function ($env, $string, $charset): string {
      return htmlspecialchars($string, ENT_XML1, $charset);
    };
    $escaperExt->setEscaper('xml', $escapeXml);

    // Adapted from https://daringfireball.net/projects/mt-escapeforjson/
    $escapeJson = function ($env, $string, $charset): string {
      if ($charset !== 'UTF-8') {
        throw new \RuntimeException('Please use UTF-8');
      }
      $string = strtr($string, self::JSON_ESCAPABLES);
      // strtr recommends having all keys be same length; these are longer
      // as they need multiple UTF-8 bytes to encode them
      $string = strtr($string, [
        "\u{2028}" => '\\u2028',
        "\u{2029}" => '\\u2029',
      ]);
      return $string;
    };
    $escaperExt->setEscaper('json', $escapeJson);

    return $environment;
  }

  public function buildIndexes(Site $site)
  {
    $this->getSiteEnvironment($site);
    $loader = $this->siteLoaders[$site->id];

    $posts = Post::selectAll([
      'state' => Post::STATE_PUBLISHED,
      'site_id' => $site->id,
    ]);

    foreach ($loader->getAllTemplates(Template::TYPE_INDEX) as $template) {
      $this->buildIndexTemplate($site, $template, $posts);
    }
  }

  public function buildIndexTemplate(
    Site $site,
    Template $template,
    array $posts
  ) {
    $twigTemplate = $this->getSiteEnvironment($site)->load($template->name);
    $contents = $twigTemplate->render(['posts' => $posts]);

    $name = $template->name;
    if (DIRECTORY_SEPARATOR !== '/') {
      $name = str_replace('/', DIRECTORY_SEPARATOR, $name);
    }
    $target = path_join($site->target_path, $name);
    file_write($target, $contents);
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
    file_write($targetPath, $content);
  }

  public function buildPhantomPost(Site $site, Post $post): string
  {
    $targetPath = path_join($site->target_path, '_preview.html');
    file_write($targetPath, $this->renderPost($site, $post));
    return '_preview.html';
  }

  public function renderPost(Site $site, Post $post)
  {
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
