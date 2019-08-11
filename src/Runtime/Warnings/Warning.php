<?php declare(strict_types=1);
namespace PN\B3\Runtime\Warnings;

class Warning
{
  public $message, $call, $file, $line;

  public function __construct(
    string $message,
    array $call,
    string $file,
    int $line
  ) {
    $this->message = $message;
    $this->call = $call;
    $this->file = $file;
    $this->line = $line;
  }
}
