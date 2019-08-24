<?php declare(strict_types=1);
namespace PN\B3\Core;
use PN\B3\App;
use PN\B3\Db\{Migrator, Sqlite};
use PN\B3\Settings\Item as Setting;
use PN\B3\Util\Singleton;
use function PN\B3\path_join;

class Site
{
  use Singleton;

  public $db;
  public function __construct()
  {
    $this->db = new Sqlite(path_join(App::ROOT, 'b3site.db'));
    Migrator::migrate($this->db,
      path_join(App::ROOT, 'etc', 'migrations', 'site'));
  }

  public function getPosts(array $criteria = [ ]): array
  {
    $type = $criteria['type'] ?? 'published';
    if ($type !== 'published' && $type !== 'all') {
      throw new \RuntimeException("Unknown post criterion: {$type}");
    }

    $createPost = function (array $item): Post {
      return new Post($item);
    };

    $query = 'select id, author_id, state, slug, title, published_at, ' .
       'modified_at, content_type';
    if ($criteria['with_content'] ?? false) {
      $query .= ', content';
    }
    $query .= ' from posts';

    $where = [ ];
    $params = [ ];

    if ($type === 'published') {
      $where[] = "state = 'published'";
    }

    $before = $item['published_before'] ?? null;
    if ($before !== null) {
      $where[] = 'published_at < :published_before';
      $params[':published_before'] = $item['published_before'];
    }

    if ($where !== [ ]) {
      $query .= ' where ' . implode(' and ', $where);
    }

    $query .= ' order by published_at desc';

    if (($count = $item['count'] ?? 30) !== null) {
      $query .= ' limit :limit';
      $params[':limit'] = $count;
    }

    $posts = $this->db->select($query, $params);
    $posts = array_map($createPost, $posts);

    $seen = [ ];
    foreach ($posts as $post) {
      $seen[$post->id] = true;
    }

    if ($posts === [ ]) {
      $cursor = null;
    } else {
      // To implement pagination properly, we add posts that were published in
      // the same second as the last one on this page, so the next page starts
      // immediately after the last one on this page.
      $last = $posts[count($posts) - 1];
      $extraPosts = $this->db->select(
        'select * from posts where published_at = :published_at and id != :id',
        [':id' => $last->id, ':published_at' => $last->publishedAt]);
      foreach ($extraPosts as $post) {
        if ( ! array_key_exists($post->id, $seen)) {
          $posts[] = new Post($post);
        }
      }

      $cursor = $last->publishedAt;
      $hasMore = $this->db->selectOne(
        'select 1 from posts where published_at < :published_before limit 1',
        [':published_before' => $cursor]) !== null;
      if ( ! $hasMore) {
        $cursor = null;
      }
    }

    return [$posts, $cursor];
  }

  public function getPost(int $id): ?Post
  {
    $post = $this->db->selectOne('select * from posts where id = :id',
      [':id' => $id]);
    if ($post !== null) {
      $post = new Post($post);
    }
    return $post;
  }

  public function getBaseUrl(): string
  {
    $url = $this->db->selectOne(
      'select value from site_meta where key = :key',
      [':key' => 'b3_site_base_url']);
    return unserialize($url['value']);
  }

  public function getSettings(): array
  {
    $settings = [
      'b3_site_root' => new Setting('b3_site_root',
        path_join(App::ROOT, 'site'),
        'Site Root',
        'The path where the index file for this site will be located.'),
      'b3_site_base_url' => new Setting('b3_site_base_url',
        'https://example.com',
        'Site Base URL',
        "The URL where the site's `index.html` will be located (without the " .
          '`index.html`.)'),
      'b3_site_title' => new Setting('b3_site_title',
        'Example Site',
        'Site Title',
        'The title of the site.'),
    ];

    $savedSettings = $this->db->select('select key, value from site_meta');
    foreach ($savedSettings as $savedSetting) {
      $key = $savedSetting['key'];
      if (array_key_exists($key, $settings)) {
        $value = unserialize($savedSetting['value']);
        $settings[$key]->value = $value;
      }
    }

    return $settings;
  }

  public function updateSettings(array $settings)
  {
    $this->db->execute('begin transaction');
    foreach ($settings as $setting) {
      $this->db->execute('insert or replace into site_meta ' .
        '(key, value) values (:key, :value)',
        [':key' => $setting->key, ':value' => serialize($setting->value)]);
    }
    $this->db->execute('commit');
  }

  public function toArray(): array
  {
    // TODO
    $settings = $this->getSettings();
    return [
      'title' => $settings['b3_site_title']->value,
      'base_url' => $this->getBaseUrl(),
    ];
  }
}
