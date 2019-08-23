<?php declare(strict_types=1);
namespace PN\B3\Db;

interface Queryable
{
  public function escapeIdentifier(string $id): string;
  public function lastInsertRowId(): ?int;
  public function execute(string $query, array $params = [ ]);
  public function select(string $query, array $params = [ ]): array;
  public function selectOne(string $query, array $params = [ ]): ?array;
}
