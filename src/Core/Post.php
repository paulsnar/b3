<?php declare(strict_types=1);
namespace PN\B3\Core;
use PN\B3\Models\BaseModel;

class Post extends BaseModel
{
  protected const TABLE = 'posts';

  const
    STATE_DRAFT = 'draft',
    STATE_PUBLISHED = 'published',
    VALID_STATES = [self::STATE_DRAFT, self::STATE_PUBLISHED];

  public
    $authorId,
    $state,
    $slug,
    $title,
    $publishedAt,
    $modifiedAt,
    $content,
    $contentType,
    $contentRendered;

  public static function isValidState(string $state): bool
  {
    return in_array($state, static::VALID_STATES);
  }

  public function getUrl(): string
  {
    $publishedAt = new \DateTime(
      '@' . $this->publishedAt, timezone_open('UTC'));

    // TODO: don't hardcode url generation !!!
    return $publishedAt->format('Y/m') . '/' . $this->slug;
  }

  public function getBody(): ?string
  {
    return $this->contentRendered;
  }
}
