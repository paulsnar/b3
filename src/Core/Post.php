<?php declare(strict_types=1);
namespace PN\B3\Core;
use function PN\B3\str_starts_with;

class Post
{
  public $filename;

  public $metadata = [ ];
  public $title, $url, $content, $body;
  public $publishedAt, $modifiedAt;

  protected const DATE_FORMAT = 'Y-m-d H:i:s P';

  public function __construct(
    string $filename,
    array $metadata,
    string $content
  ) {
    $this->filename = $filename;
    $this->metadata = $metadata;
    $this->content = $content;

    if (array_key_exists('title', $metadata)) {
      $this->title = $metadata['title'];
    }
    if (array_key_exists('published-at', $metadata)) {
      $this->publishedAt = $metadata['published-at'];
      if (is_string($this->publishedAt)) {
        $this->publishedAt = \DateTime::createFromFormat(
          static::DATE_FORMAT, $this->publishedAt);
      }
    }
    if (array_key_exists('modified-at', $metadata)) {
      $this->modifiedAt = $metadata['modified-at'];
      if (is_string($this->modifiedAt)) {
        $this->modifiedAt = \DateTime::createFromFormat(
          static::DATE_FORMAT, $this->modifiedAt);
      }
    }

    $this->url = $this->computeUrl();
  }

  private function computeUrl()
  {
    if (array_key_exists('slug', $this->metadata)) {
      $slug = $this->metadata['slug'];
    } else {
      $slug = $this->title;
      $slug = preg_replace('/[^A-Za-z0-9]+/', '-', $slug);
      $slug = strtolower($slug);
    }

    return '/' . $this->publishedAt->format('Y/m') . '/' . $slug;
  }

  private static function parseMetadata(string $metadata)
  {
    $lines = explode("\n", $metadata);

    $pairs = array_map(function ($pair) {
      if ($pair === '') {
        return null;
      }
      if (strpos($pair, '=') === false) {
        throw new \RuntimeException(
          'Malformed metadata pair: no equals sign: ' . $pair);
      }

      $pair = explode('=', $pair, 2);
      return array_map('trim', $pair);
    }, $lines);

    $pairs = array_filter($pairs, function ($pair) { return $pair !== null; });

    return array_reduce($pairs, function ($metadata, $pair) {
      [$name, $value] = $pair;
      $metadata[$name] = json_decode($value, true);
      return $metadata;
    }, [ ]);
  }

  public static function loadFromFile(string $filename)
  {
    $content = file_get_contents($filename);
    $meta = [ ];
    if (str_starts_with($content, "---\n")) {
      $metaStart = 4;
      $metaEnd = strpos($content, "\n---\n", 4);
      if ($metaEnd !== false) {
        $meta = substr($content, $metaStart, $metaEnd - $metaStart);
        $meta = static::parseMetadata($meta);
        $content = substr($content, $metaEnd + 5);
      }
    }

    $post = new static($filename, $meta, $content);
    return $post;
  }

  public function isVisible(): bool
  {
    if (array_key_exists('is-visible', $this->metadata)) {
      return $this->metadata['is-visible'];
    }
    return true;
  }

  public function toArray()
  {
    $post = [
      'url' => $this->url,
      'title' => $this->title,
      'body' => $this->body,
      'published_at' => $this->publishedAt,
    ];
    if ($this->modifiedAt !== null) {
      $post['modified_at'] = $this->modifiedAt;
    }

    return $post;
  }
}
