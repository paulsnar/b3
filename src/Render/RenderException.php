<?php declare(strict_types=1);
namespace PN\Blog\Render;

class RenderException extends \Exception
{
  public function __construct(
    string $message,
    string $originatingFile,
    ?\Throwable $previous = NULL
  ) {
    parent::__construct($message, 0, $previous);
    $this->file = $originatingFile;
    $this->line = 0;
  }
}
