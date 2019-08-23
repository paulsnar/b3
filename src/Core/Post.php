<?php declare(strict_types=1);
namespace PN\B3\Core;
use PN\B3\Models\BaseModel;

class Post extends BaseModel
{
  protected const TABLE = 'posts';

  const
    STATE_DRAFT = 'draft',
    STATE_PUBLISHED = 'published',
    VALID_STATES = ['draft', 'published'];

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
}
