<?php declare(strict_types=1);
namespace PN\B3\Db;
use function PN\B3\str_maskpos;

class Sqlite implements Queryable
{
  protected $db;

  public function __construct(string $path)
  {
    $this->db = new \SQLite3($path);
  }

  public function escapeIdentifier(string $id): string
  {
    if (str_maskpos($id, ["'", '"', '`', ' ']) !== false) {
      $id = strtr($id, ['"' => '""']);
      $id = '"' . $id . '"';
    }
    return $id;
  }

  public function lastInsertRowId(): ?int
  {
    $id = $this->db->lastInsertRowId();
    if ($id === 0) {
      return null;
    }
    return $id;
  }

  protected function query(string $query, array $params = [ ])
  {
    $stmt = $this->db->prepare($query);
    if ($stmt === false) {
      throw new \RuntimeException($this->db->lastErrorMsg());
    }

    foreach ($params as $key => $value) {
      if (is_integer($key)) {
        $key += 1;
      }
      if ( ! $stmt->bindValue($key, $value)) {
        throw new \RuntimeException($this->db->lastErrorMsg());
      }
    }

    $result = $stmt->execute();
    if ($result === false) {
      throw new \RuntimeException($this->db->lastErrorMsg());
    }

    return [$stmt, $result];
  }

  public function execute(string $query, array $params = [ ])
  {
    if ($params === [ ]) {
      if ( ! $this->db->exec($query)) {
        throw new \RuntimeException($this->db->lastErrorMsg());
      }
    } else {
      $this->query($query, $params);
    }
  }

  public function select(string $query, array $params = [ ]): array
  {
    [$stmt, $result] = $this->query($query, $params);
    $rows = [ ];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
      $rows[] = $row;
    }
    try {
      return $rows;
    } finally {
      $result->finalize();
      $stmt->close();
    }
  }

  public function selectOne(string $query, array $params = [ ]): ?array
  {
    [$stmt, $result] = $this->query($query, $params);
    $row = $result->fetchArray(SQLITE3_ASSOC);
    if ($row === false) {
      $row = null;
    }
    try {
      return $row;
    } finally {
      $result->finalize();
      $stmt->close();
    }
  }
}
