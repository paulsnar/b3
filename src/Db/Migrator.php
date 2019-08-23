<?php declare(strict_types=1);
namespace PN\B3\Db;
use function PN\B3\{dir_scan, path_join};

class Migrator
{
  public static function migrate(Queryable $db, string $migrationRoot)
  {
    try {
      $row = $db->selectOne(
        "select value from db_meta where key = 'schema_version'");
      $schemaVersion = $row['value'];
    } catch (\Throwable $exc) {
      $schemaVersion = -1;
    }

    $maxSchemaVersion = 0;
    $upSources = [0 => '000-base.sql'];
    foreach (dir_scan($migrationRoot) as $migration) {
      $migrationName = substr($migration, strlen($migrationRoot));
      $nameParts = explode('-', $migrationName, 3);
      $version = intval($nameParts[0], 10);
      if ($version === 0) {
        continue;
      }
      $type = $nameParts[1];
      if ($type === 'up') {
        $upSources[$version] = $migration;
      }
      $maxSchemaVersion = max($maxSchemaVersion, $version);
    }

    if ($schemaVersion < $maxSchemaVersion) {
      for ($i = $schemaVersion + 1; $i <= $maxSchemaVersion; $i += 1) {
        static::applyMigration($db, file_get_contents($upSources[$i]));
      }
    }
  }

  protected static function applyMigration(Queryable $db, string $script)
  {
    // This is a very crude hack to apply multiple consecutive SQL statements
    // unto a database which only supports a single statement at a time.
    $statements = explode(';', $script);
    foreach ($statements as $statement) {
      $statement = trim($statement);
      $db->execute($statement);
    }
  }
}
