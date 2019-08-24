<?php declare(strict_types=1);
namespace PN\B3\Templating;
use PN\B3\Db\DbObject;

class Template extends DbObject
{
  protected const TABLE = 'templates';
  protected const COLUMNS = [
    'site_id' => 'integer',
    'type' => 'string',
    'name' => 'string',
    'dependencies' => 'json',
    'modified_at' => 'integer',
    'content' => 'string',
  ];

  const
    TYPE_INDEX = 'index',
    TYPE_ENTRY = 'entry',
    TYPE_AMBIENT = null;

  protected const QUERY_TEMPLATE_AND_DEPENDENCIES = <<<'SQL'
with recursive dependencies(name) as
  (values :template_name) union
   select td.value
    from templates t, json_each(t.dependencies) td, dependencies d
    where t.name = d.name)
select t.id, t.type, t.name, t.content from templates t, dependencies d
  where t.name = d.name
SQL;

  public static function loadWithDependencies(string $name)
  {
    $templates = $db->select(static::QUERY_TEMPLATE_AND_DEPENDENCIES,
      [':template_name' => $name]);
    return array_map(function ($template) {
      return new static($template);
    }, $templates);
  }

  public function getSystemName(): string
  {
  }

  public function getTargetDirectory(): string
  {
  }

  public function getTargetPath(): string
  {
  }
}
