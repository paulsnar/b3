<?php declare(strict_types=1);
namespace PN\B3\Core;
use PN\B3\App;
use PN\B3\Db\{DbObject, Migrator, Sqlite};
use PN\B3\Settings;
use PN\B3\Templating\Theme;
use function PN\B3\path_join;

class Site extends DbObject
{
  const TABLE = 'sites';
  const COLUMNS = [
    'title' => 'string',
    'base_url' => 'string',
    'target_path' => 'string',
  ];
}
