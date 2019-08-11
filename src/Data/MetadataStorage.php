<?php declare(strict_types=1);
namespace PN\B3\Data;
use function PN\B3\path_join;

class MetadataStorage
{
  protected $db;

  public function __construct(string $root)
  {
    $this->db = new \SQLite3(path_join($root, 'metadata.db'));
  }
}
