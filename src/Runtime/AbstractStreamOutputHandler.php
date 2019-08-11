<?php declare(strict_types=1);
namespace PN\B3\Runtime;

abstract class AbstractStreamOutputHandler implements OutputHandlerInterface
{
  protected $fd;

  public function __construct()
  {
    $this->fd = $this->openStream();
  }

  protected abstract function openStream();

  public function output(string $message)
  {
    fwrite($this->fd, $message);
  }
}
