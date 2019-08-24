<?php declare(strict_types=1);
namespace PN\B3;
use function PN\B3\path_join;

class Config
{
  use Util\Singleton;

  protected $values = [ ], $dirty = false;

  public function __construct()
  {
    $app = App::getInstance();

    $path = path_join(App::ROOT, 'b3config.php');
    if ( ! file_exists($path)) {
      $app->dispatchEvent('b3.loadconfigdefaults');
      $this->export();
    } else {
      $this->values = require $path;
    }

    $app->addEventListener('b3.shutdown', function () {
      $this->handleShutdown();
    });
  }

  public function getValue(string $key, $default = null)
  {
    return $this->values[$key] ?? $default;
  }

  public function setValue(string $key, $value)
  {
    if ( ! array_key_exists($key, $this->values) ||
        $value !== $this->values[$key]) {
      $this->values[$key] = $value;
      $this->dirty = true;
    }
  }

  protected function handleShutdown()
  {
    if ($this->dirty) {
      $this->export();
    }
  }

  protected function export()
  {
    $body = "<?php return " . var_export($this->values, true) . ";\n";

    $umask = umask();
    umask(0037);

    $target = fopen(path_join(App::ROOT, 'b3config.php'), 'w');
    flock($target, LOCK_EX);
    fwrite($target, $body);
    fflush($target);
    flock($target, LOCK_UN);
    fclose($target);
  }

  public static function get(string $key, $default = null)
  {
    return static::getInstance()->getValue($key, $default);
  }

  public static function set(string $key, $value)
  {
    return static::getInstance()->setValue($key, $value);
  }

  public static function path(string $key)
  {
    $base = App::ROOT;
    $path = static::getInstance()->getValue($key);
    if ($path === null) {
      throw new \RuntimeException("Cannot get config key: {$key}");
    }
    $path = explode('/', $path);
    return path_join($base, ...$path);
  }
}
