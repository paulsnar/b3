<?php declare(strict_types=1);
namespace PN\B3\Models;
use PN\B3\Db\Queryable;
use function PN\B3\{iter_collect, obj_get_properties};

abstract class BaseModel
{
  public static function lookup(Queryable $db, array $attributes): ?self
  {
    $query = 'select * from ' . static::TABLE . ' where ';

    $whereClauses = [ ];
    $queryParams = [ ];
    foreach ($attributes as $name => $value) {
      $name = $db->escapeIdentifier($name);
      $whereClauses[] = "{$name} = ?";
      $queryParams[] = $value;
    }
    $query .= implode(' and ', $whereClauses);

    $row = $db->selectOne($query, $queryParams);
    if ($row === null) {
      return null;
    }
    return new static($row);
  }

  public function __construct(array $params)
  {
    foreach ($params as $key => $value) {
      $key = static::snakeCaseToCamelCase($key);
      $this->{$key} = $value;
    }
  }

  protected static function snakeCaseToCamelCase(string $snakeCase): string
  {
    $parts = explode('_', $snakeCase);
    $start = array_shift($parts);
    $parts = array_map('ucfirst', $parts);
    return $start . implode('', $parts);
  }

  protected static function camelCaseToSnakeCase(string $camelCase): string
  {
    $parts = [ ];
    $part = '';
    $l = strlen($camelCase);
    for ($i = 0; $i < $l; $i += 1) {
      $char = $camelCase[$i];
      if (ctype_upper($char)) {
        $parts[] = $part;
        $part = strtolower($char);
      } else {
        $part .= $char;
      }
    }
    $parts[] = $part;
    return implode('_', $parts);
  }

  public function toArray(): array
  {
    $array = obj_get_properties($this);
    return iter_collect(function () use ($array) {
      foreach ($array as $key => $value) {
        $key = static::camelCaseToSnakeCase($key);
        yield $key => $value;
      }
    });
  }

  public static function insert(Queryable $db, array $params): self
  {
    $query = 'insert into ' . static::TABLE . ' (';

    $columns = [ ];
    $values = [ ];

    foreach ($params as $name => $value) {
      $columns[] = $db->escapeIdentifier($name);
      $values[] = $value;
    }
    $query .= implode(', ', $columns);
    $query .= ') values (';
    $query .= implode(', ', array_fill(0, count($columns), '?'));
    $query .= ')';

    $db->execute($query, $values);

    $params['id'] = $db->lastInsertRowId();
    return new static($params);
  }

  public function delete(Queryable $db)
  {
    $db->execute('delete from ' . static::TABLE . ' where id = :id',
      [':id' => $this->id]);
  }

  public function update(Queryable $db, ?array $params = null)
  {
    $query = 'update ' . static::TABLE . ' set ';

    $items = [ ];
    $values = [ ];

    if ($params === null) {
      foreach (obj_get_public_vars($this) as $name => $value) {
        if ($name === 'id') {
          continue;
        }
        $name = static::camelCaseToSnakeCase($name);
        $name = $db->escapeIdentifier($name);
        $items[] = "{$name} = ?";
        $values[] = $value;
      }
    } else {
      foreach ($params as $name => $value) {
        if ($name === 'id') {
          continue;
        }
        $name = $db->escapeIdentifier($name);
        $items[] = "{$name} = ?";
        $values[] = $value;
      }
    }

    $query .= implode(', ', $items);
    $query .= ' where id = ?';
    $values[] = $this->id;

    $db->execute($query, $values);

    if ($params !== null) {
      foreach ($params as $name => $value) {
        $name = static::snakeCaseToCamelCase($name);
        $this->{$name} = $value;
      }
    }
  }
}
