<?php declare(strict_types=1);
namespace PN\B3\Db;

class SqlPlaceholder
{
  protected $sql, $values;

  public function __construct(string $sql, array $values)
  {
    $this->sql = $sql;
    $this->values = $values;
  }

  public function getSql(): string
  {
    return $this->sql;
  }

  public function getValues(): array
  {
    return $this->values;
  }
}
