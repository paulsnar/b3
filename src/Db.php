<?php declare(strict_types=1);
namespace PN\B3;
use PN\B3\Db\{Migrator, Queryable, Sqlite};

abstract class Db
{
  protected static $globalDb;
  public static function getGlobal(): Queryable
  {
    if (static::$globalDb === null) {
      $path = path_join(App::ROOT, 'b3.db');
      static::$globalDb = new Sqlite($path);

      $migrations = path_join(App::ROOT, 'etc', 'migrations');
      Migrator::migrate(static::$globalDb, $migrations);
    }

    return static::$globalDb;
  }
}
