<?php declare(strict_types=1);
namespace PN\B3\Core;
use PN\B3\Db;
use PN\B3\Db\{DbObject, Queryable, SqlPlaceholder};
use function PN\B3\{array_index, array_without};

class Post extends DbObject
{
  protected const TABLE = 'posts';
  protected const COLUMNS = [
    'author_id' => 'integer',
    'site_id' => 'integer',
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

  protected static function buildPostSelect(
    array $criteria,
    Queryable $db
  ): array {
    $columns = ['id', 'author_id', 'site_id', 'state', 'slug', 'title',
      'published_at', 'modified_at', 'content_type'];
    if ($criteria['include_content'] ?? false) {
      $columns[] = 'content';
    }

    $attributes = [ ];

    $site = array_index($criteria, 'site_id', null);
    if ($site !== null) {
      $attributes['site_id'] = $site;
    }

    $state = array_index($criteria, 'state', self::STATE_PUBLISHED);
    if ($state !== null && self::isValidState($state)) {
      $attributes['state'] = $state;
    }

    $at = $criteria['published_at'] ?? null;
    $before = $criteria['published_before'] ?? null;
    if ($at !== null) {
      $attributes['published_at'] = $at;
    } else if ($before !== null) {
      $attributes['published_at'] = ['<', $before];
    }

    if (array_key_exists('id_not_in', $criteria)) {
      $ids = $criteria['id_not_in'];
      $fragment = new SqlPlaceholder(
        '(' . implode(', ', array_fill(0, count($ids), '?')) . ')',
        $ids);
      $attributes['id'] = ['not in', $fragment];
    }

    [$query, $params] = static::buildSelect($attributes, $columns, $db);
    $query .= ' order by published_at desc';

    $limit = array_index($criteria, 'count', 30);
    if ($limit !== null) {
      $query .= ' limit :limit';
      $params[':limit'] = $limit;
    }

    return [$query, $params];
  }

  public static function selectAll(
    array $criteria = [ ],
    ?Queryable $db = null
  ): array {
    if ($db === null) {
      $db = Db::getGlobal();
    }

    [$query, $params] = self::buildPostSelect($criteria, $db);
    $posts = $db->select($query, $params);

    if (count($posts) === 0) {
      return $posts;
    }

    $deserialize = function (array $item): self {
      return new self($item);
    };
    $posts = array_map($deserialize, $posts);

    /* If the previous query was limited, in order for the results to be
     * reliably cursorable (and it's presumed that they will be cursored by
     * the timestamp), we add to the current results posts which are posted on
     * the same second as the last one.
     *
     * Note that due to this the count parameter might be disrespected and the
     * consumer might receive slightly more items than they request. Hopefully
     * this won't be an issue, since the chance of two posts being posted at
     * the same second is miniscule. The tech of this engine will probably have
     * failed you long before you reach 5 posts per second :) */
    if (array_key_exists('count', $criteria)) {
      $ignore = [ ];
      $cursor = null;
      for ($i = count($posts) - 1; $i >= 0; $i -= 1) {
        $post = $posts[$i];
        if ($cursor === null) {
          $ignore[] = $post->id;
          $cursor = $post->published_at->getTimestamp();
          continue;
        }
        $timestamp = $post->published_at->getTimestamp();
        if ($timestamp !== $cursor) {
          break;
        }
        $ignore[] = $post->id;
      }

      // This next query is unlimited because we need *all* the posts at the
      // same second. Also remove the remaining time constraint.
      $criteria = array_without($criteria, 'count', 'published_before');
      $criteria['published_at'] = $cursor;
      $criteria['id_not_in'] = $ignore;
      [$query, $params] = self::buildPostSelect($criteria, $db);
      $posts2 = $db->select($query, $params);
      $posts2 = array_map($deserialize, $posts2);
      $posts = array_merge($posts, $posts2);
    }

    return $posts;
  }
}
