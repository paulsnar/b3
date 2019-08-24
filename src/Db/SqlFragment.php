<?php declare(strict_types=1);
namespace PN\B3\Db;

class SqlFragment
{
  protected $sql;

  public function __construct(string $sql)
  {
    $this->sql = $sql;
  }

  public function toString(): string
  {
    return $this->sql;
  }
}
