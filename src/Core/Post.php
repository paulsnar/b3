<?php declare(strict_types=1);
namespace PN\B3\Core;
use PN\B3\Db\DbObject;

class Post extends DbObject
{
  protected const TABLE = 'posts';
  protected const COLUMNS = [
    'author_id' => 'integer',
    'state' => 'string',
    'slug' => 'string',
    'title' => 'string',
    'published_at' => 'timestamp',
    'modified_at' => 'timestamp',
    'content' => 'string',
    'content_type' => 'string',
  ];

  const
    STATE_DRAFT = 'draft',
    STATE_PUBLISHED = 'published',
    VALID_STATES = [self::STATE_DRAFT, self::STATE_PUBLISHED];

  public static function isValidState(string $state): bool
  {
    return in_array($state, static::VALID_STATES, true);
  }

  public function getUrl(): string
  {
    // TODO: don't hardcode url generation?
    return $this->published_at->format('Y/m') . '/' . $this->slug;
  }
}
