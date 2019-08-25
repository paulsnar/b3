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
    'modified_at' => 'integer',
    'content' => 'string',
  ];

  const
    TYPE_INDEX = 'index',
    TYPE_ENTRY = 'entry',
    TYPE_AMBIENT = 'ambient',
    VALID_TYPES = [self::TYPE_INDEX, self::TYPE_ENTRY, self::TYPE_AMBIENT];

  public static function isValidType(string $type): bool
  {
    return in_array($type, self::VALID_TYPES);
  }
}
