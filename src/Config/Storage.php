<?php declare(strict_types=1);
namespace PN\B3\Config;
use function PN\B3\path_join;

class Storage
{
  protected $db;
  protected $stmtGet, $stmtSet;

  public function __construct()
  {
    $
    $path = path_join(dirname(dirname(__DIR__)), 'b3config.db');
    $this->db = new \SQLite3($path);

    if ( ! $this->schemaExists()) {
      $this->createSchema();
    }

    $this->stmtGet = $this->guard($this->db->prepare(
      'select value from config_values where key = :key'));
    $this->stmtSet = $this->guard($this->db->prepare(
      'insert or replace into config_values (key, value) ' .
        'values (:key, :value)'));
  }

  protected function schemaExists(): bool
  {
    try {
      $stmt = $this->db->prepare('select 1 from config_values');
      $stmt->close();
      return true;
    } catch (\Throwable $err) {
      return false;
    }
  }

  protected function guard($ok)
  {
    if ( ! $ok) {
      throw new \RuntimeException($this->db->lastErrorMsg());
    }
    return $ok;
  }

  protected function createSchema()
  {
    $this->guard($this->db->exec('pragma journal_mode=wal'));
    $this->guard(
      $this->db->exec('create table if not exists config_values ' .
        '(key string primary key, value string)'));
  }

  public function get(string $key, $default = null)
  {
    $result = false;
    try {
      $this->guard($this->stmtGet->bindValue(':key', $key));
      $this->guard($result = $this->stmtGet->execute());
      $row = $result->fetchArray(SQLITE3_ASSOC);

      if ($row === false) {
        return $default;
      } else {
        return unserialize($row['value']);
      }
    } finally {
      if ($result !== false) {
        $result->finalize();
      }
      $this->stmtGet->reset();
      $this->stmtGet->clear();
    }
  }

  public function set(string $key, $value)
  {
    $value = serialize($value);
    $result = false;

    try {
      $this->guard($this->stmtSet->bindValue(':key', $key));
      $this->guard($this->stmtSet->bindValue(':value', $value));
      $this->guard($result = $this->stmtSet->execute());
      return $value;
    } finally {
      if ($result !== false) {
        $result->finalize();
      }
      $this->stmtSet->reset();
      $this->stmtSet->clear();
    }
  }

  public function transaction(\Closure $callback)
  {
    $this->db->exec('begin transaction');
    try {
      $callback($this);
      $this->db->exec('commit');
    } catch (\Throwable $exc) {
      $this->db->exec('rollback');
      throw $exc;
    }
  }
}
