<?php declare(strict_types=1);
namespace PN\B3\Db;
use PN\B3\Db;
use PN\B3\JsonSerializable;

abstract class DbObject
{
  // const TABLE
  // const COLUMNS

  protected static function buildSelect(
    array $attributes,
    ?array $columns = null,
    Queryable $db
  ): array {
    if ($columns === null) {
      $columns = [new SqlFragment('*')];
    }
    $columns = array_map(function ($name) use ($db) {
      if ($name instanceof SqlFragment) {
        return $name->toString();
      }
      return $db->escapeIdentifier($name);
    }, $columns);
    $query = 'select ' . implode(', ', $columns) . ' from ' . static::TABLE;
    $params = [ ];

    if ($attributes !== [ ]) {
      $query .= ' where ';

      $whereClauses = [ ];
      foreach ($attributes as $name => $value) {
        $name = $db->escapeIdentifier($name);
        $operator = '=';
        $param = '?';
        if (is_array($value)) {
          [$operator, $value] = $value;
        }
        if ($value instanceof SqlFragment) {
          $param = $value->toString();
        } else if ($value instanceof SqlPlaceholder) {
          $param = $value->getSql();
          $params = array_merge($params, $value->getValues());
        } else {
          $params[] = $value;
        }
        $whereClauses[] = "{$name} {$operator} {$param}";
      }
      $query .= implode(' and ', $whereClauses);
    }

    return [$query, $params];
  }

  public static function exists(array $attributes, ?Queryable $db = null): bool
  {
    if ($db === null) {
      $db = Db::getGlobal();
    }

    [$query, $params] = static::buildSelect(
      $attributes, [new SqlFragment('1')], $db);
    $query .= ' limit 1';

    return $db->selectOne($query, $params) !== null;
  }

  public static function lookup(array $attributes, ?Queryable $db = null): ?self
  {
    if ($db === null) {
      $db = Db::getGlobal();
    }

    [$query, $params] = static::buildSelect($attributes, null, $db);
    $query .= ' limit 1';

    $row = $db->selectOne($query, $params);
    if ($row !== null) {
      return new static($row);
    }
    return $row;
  }

  public static function select(array $attributes, ?Queryable $db = null): array
  {
    if ($db === null) {
      $db = Db::getGlobal();
    }

    [$query, $params] = static::buildSelect($attributes, null, $db);

    return array_map(function ($row) {
      return new static($row);
    }, $db->select($query, $params));
  }

  protected $attributes = [ ];

  public function __construct(array $params)
  {
    foreach ($params as $key => $value) {
      if ($key === 'id') {
        $this->attributes['id'] = $this->typecast('id', $value);
        continue;
      }

      if ( ! array_key_exists($key, static::COLUMNS)) {
        continue;
      }
      $this->attributes[$key] = $this->typecast($key, $value);
    }
  }

  protected function typecast(string $key, $value)
  {
    if ($value === null) {
      return null;
    }

    if ($key === 'id') {
      $type = 'integer';
    } else {
      $type = static::COLUMNS[$key] ?? 'string';
    }

    switch ($type) {
      case 'string':
        return strval($value);

      case 'integer':
        return intval($value, 10);

      case 'boolean':
        return intval($value, 10) === 1 || $value === 't';

      case 'timestamp':
        return new \DateTime('@' . $value, timezone_open('UTC'));

      case 'json':
        return json_decode($value, true);
    }

    return $value;
  }

  protected function typeUncast(string $key, $value)
  {
    if ($value === null) {
      return null;
    }

    if ($key === 'id') {
      $type = 'integer';
    } else {
      $type = static::COLUMNS[$key] ?? 'string';
    }

    switch ($type) {
      case 'timestamp':
        return $value->getTimestamp();

      case 'json':
        return json_encode($value, JSON_UNESCAPED_SLASHES);
    }
  }

  public function toArray(): array
  {
    $array = [ ];
    foreach (static::COLUMNS as $name => $_) {
      $array[$name] = $this->attributes[$name] ?? null;
    }
    return $array;
  }

  public function serializeJson()
  {
    return $this->toArray();
  }

  public function __get(string $key)
  {
    if (array_key_exists($key, $this->attributes)) {
      return $this->attributes[$key];
    }
    $method = 'get' . ucfirst($key);
    if (method_exists($this, $method)) {
      try {
        return $this->{$method}();
      } catch (\Throwable $err) { }
    }
    return null;
  }

  public function __set(string $key, $value)
  {
    $this->attributes[$key] = $value;
  }

  public function __isset(string $key): bool
  {
    return array_key_exists($key, $this->attributes);
  }

  public function __unset(string $key)
  {
    if (array_key_exists($key, $this->attributes)) {
      unset($this->attributes[$key]);
    }
  }

  public static function insert(array $attributes, ?Queryable $db = null): self
  {
    if ($db === null) {
      $db = Db::getGlobal();
    }

    $query = 'insert into ' . static::TABLE . ' (';

    $columns = [ ];
    $values = [ ];

    foreach ($attributes as $key => $value) {
      $columns[] = $db->escapeIdentifier($key);
      $values[] = $value;
    }

    $query .= implode(', ', $columns);
    $query .= ') values (';
    $query .= implode(', ', array_fill(0, count($values), '?'));
    $query .= ')';

    $db->execute($query, $values);

    $attributes['id'] = $db->lastInsertRowId();
    return new static($attributes);
  }

  public function delete(?Queryable $db = null)
  {
    if ($db === null) {
      $db = Db::getGlobal();
    }
    $db->execute(
      'delete from ' . static::TABLE . ' where id = :id',
      [':id' => $this->id]);
  }

  public function update(?array $params = null, ?Queryable $db = null)
  {
    if ($db === null) {
      $db = Db::getGlobal();
    }

    $query = 'update ' . static::TABLE . ' set ';

    $items = [ ];
    $values = [ ];

    if ($params === null) {
      $params = $this->attributes;
      unset($params['id']);
    }

    foreach ($params as $name => $value) {
      $name = $db->escapeIdentifier($name);
      $items[] = "{$name} = ?";
      $values[] = $value;
    }

    $query .= implode(', ', $items);
    $query .= ' where id = ?';
    $values[] = $this->id;

    $db->execute($query, $values);

    foreach ($params as $name => $value) {
      $this->attributes[$name] = $value;
    }
  }
}
