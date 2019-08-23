<?php declare(strict_types=1);
namespace PN\B3;
use function PN\B3\path_join;

class Config
{
  use Util\Singleton;

  public $db;

  public function __construct()
  {
    $this->db = new Db\Sqlite(path_join(App::ROOT, 'b3config.db'));
    Db\Migrator::migrate($this->db,
      path_join(App::ROOT, 'etc', 'migrations', 'config'));

    // $this->loadDefaults();
  }

  public function getValue(string $key, $default = null)
  {
    $row = $this->db->selectOne(
      'select value from config_values where key = :key',
      [':key' => $key]);
    if ($row === null) {
      return $default;
    }
    return unserialize($row['value']);
  }

  public function setValue(string $key, $value)
  {
    $value = serialize($value);
    $this->db->execute('insert or replace into config_values ' .
      '(key, value) values (:key, :value)',
      [':key' => $key, ':value' => $value]);
  }

  public function transaction(\Closure $callback)
  {
    $this->db->execute('begin transaction');
    try {
      $callback($this);
      $this->db->execute('commit');
    } catch (\Throwable $exc) {
      $this->db->execute('rollback');
      throw $exc;
    }
  }

  public static function getDb(): Db\Queryable
  {
    return static::getInstance()->db;
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
