<?php declare(strict_types=1);
namespace PN\B3;
use PN\B3\Core\{Post, Site};

function render_php_template(string $__filename, array $__context) {
  extract($__context);
  ob_start();
  try {
    require $__filename;
    return ob_get_contents();
  } finally {
    ob_end_clean();
  }
}

(function () {
  $ROOT = dirname(__DIR__);
  chdir($ROOT);

  $WROTE_FILES = 0;

  require $ROOT . '/vendor/autoload.php';

  ensure_config();
  set_error_handler();

  $markdownRenderer = new Markdown\Renderer();
  $templateRenderer = new Template\Renderer($ROOT . '/templates');

  $site = Site::fromGlobals();

  $posts = scandir("{$ROOT}/site/posts/");
  $posts = array_map(function ($post) use ($ROOT, $markdownRenderer) {
    if ($post[0] === '.') {
      return null;
    }
    $post = Post::loadFromFile("{$ROOT}/site/posts/{$post}");
    $post->body = $markdownRenderer->render($post->content);
    return $post;
  }, $posts);
  $posts = vector_filter($posts, function ($post) { return $post !== null; });
  usort($posts, function ($a, $b) {
    return $b->publishedAt <=> $a->publishedAt;
  });

  $TARGET = $ROOT . '/public';

  $siteArray = $site->toArray();
  $postArrays = vector_filter($posts, function ($post) {
    return $post->isVisible();
  });
  $postArrays = array_map(function ($post) {
    return $post->toArray();
  }, $postArrays);

  $indexTemplates = scandir("{$ROOT}/templates/index/");
  foreach ($indexTemplates as $indexTemplate) {
    if ($indexTemplate[0] === '.') {
      continue;
    }

    $path = "{$ROOT}/templates/index/{$indexTemplate}";
    $name = explode('.', $indexTemplate);
    $extension = array_pop($name);
    $name = implode('.', $name);
    if ($extension === 'twig') {
      $result = $templateRenderer->render("index/{$indexTemplate}", [
        'site' => $siteArray,
        'posts' => $postArrays,
      ]);
    } else if ($extension === 'php') {
      $result = render_php_template($path, [
        'site' => $siteArray,
        'posts' => $postArrays,
        'url' => [$site, 'generateUrl'],
      ]);
    } else {
      throw new \RuntimeException("Unknown template extension: {$extension}");
    }

    file_put_contents("{$TARGET}/{$name}", $result);
    $WROTE_FILES += 1;
  }

  foreach ($posts as $post) {
    $targetPath = "{$TARGET}/{$post->url}.html";
    $postPage = $templateRenderer->render('archive/post.html.twig', [
      'site' => $siteArray,
      'post' => $post->toArray(),
    ]);

    $dir = $TARGET . $post->publishedAt->format('/Y/m');
    if ( ! is_dir($dir)) {
      mkdir($dir, 0755, true);
    }
    file_put_contents($targetPath, $postPage);
    $WROTE_FILES += 1;
  }

  echo "[b3] Wrote {$WROTE_FILES} files\n";
})();
