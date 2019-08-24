<?php declare(strict_types=1);
namespace PN\B3;

final class App extends Events\EventTarget
{
  use Util\Singleton;

  const PRIVATE_ROOT = __DIR__;
  const ROOT = __DIR__ . '/..';

  public static function url(string $path): string
  {
    $base = Config::get('base_url');
    if ($base === null) {
      return ltrim($path, '/');
    }
    $base = rtrim($base, '/');
    $path = ltrim($path, '/');
    return "{$base}/{$path}";
  }

  public function __construct()
  {
    parent::__construct();
  }

  protected function setErrorHandler()
  {
    set_error_handler(function ($severity, $message, $file, $line) {
      throw new \ErrorException($message, 0, $severity, $file, $line);
    });
  }

  protected function setExceptionHandler()
  {
    set_exception_handler(function (\Throwable $exc) {
      http_response_code(Http\Status::INTERNAL_SERVER_ERROR);
      try {
        header('Content-Type: text/plain; charset=UTF-8');
      } catch (\Throwable $err) { }

      ob_start();
      echo "--- Sorry, looks like something went wrong. ---\n";
      echo get_class($exc);
      if ($message = $exc->getMessage()) {
        echo ": ", $message;
      }
      echo sprintf("\n at %s:%d\n", $exc->getFile(), $exc->getLine());
      foreach ($exc->getTrace() as $i => $trace) {
        if ($trace['class'] ?? false) {
          $call = "{$trace['class']}{$trace['type']}{$trace['function']}";
        } else {
          $call = $trace['function'];
        }
        echo sprintf("%2d: %s (%s:%s)\n",
          $i, $call, $trace['file'] ?? '', $trace['line'] ?? 0);
      }

      echo "\nInstalled extensions: \n";
      $extensions = Extension\Registry::getInstance()->getExtensions();
      foreach ($extensions as $name => $ext) {
        $e = $ext['instance'];
        echo sprintf("* %s (%s)\n",
          $name,
            $ext['system'] ? 'system' :
              ($e->isEnabled ? 'enabled' : 'disabled'));
      }

      $body = ob_get_clean();
      echo str_replace("\x00", "\u{FFFD}", $body);
      file_put_contents('php://stderr', $body);
    });
  }

  public function isInstalled(): bool
  {
    return file_exists(path_join(App::ROOT, 'b3config.db'));
  }

  private $amImportingSystemExtensions = false;
  public function getInternalState(string $key)
  {
    if ($key === 'b3.is-importing-system-extension') {
      return $this->amImportingSystemExtensions;
    } else {
      throw new \RuntimeException("Unknown internal state key: {$key}");
    }
  }

  protected function importExtensions()
  {
    $this->amImportingSystemExtensions = true;
    $path = path_join(__DIR__, 'extensions');
    $extensions = dir_scan($path);
    foreach ($extensions as $extension) {
      if (is_dir($extension)) {
        $extension = path_join($extension, 'b3-extension.php');
      }
      require $extension;
    }

    $this->amImportingSystemExtensions = false;
    try {
      $path = path_join(static::ROOT, 'extensions');
      $extensions = dir_scan($path);
    } catch (\Throwable $err) {
      $extensions = [ ];
    }
    foreach ($extensions as $extension) {
      if (is_dir($extension)) {
        $extension = path_join($extension, 'b3-extension.php');
      }
      require $extension;
    }

    $this->dispatchEvent('b3.extensionsloaded');
  }

  protected function boot()
  {
    $this->setErrorHandler();
    $this->setExceptionHandler();

    $this->dispatchEvent('b3.earlyboot');

    $this->importExtensions();

    $this->dispatchEvent('b3.boot');
  }

  protected function shutdown()
  {
    $this->dispatchEvent('b3.shutdown');
  }

  public function run()
  {
    $this->boot();

    $dispatcher = Dispatch\Dispatcher::getInstance();

    // if ( ! $this->isInstalled()) {
    //   $dispatcher->addEventListener('request', function ($rq) {
    //     $rq->action = 'install';
    //   });
    // }

    $response = $dispatcher->dispatch();
    $response->send();
    if (function_exists('fastcgi_finish_request')) {
      fastcgi_finish_request();
    }

    $this->shutdown();
  }
}
