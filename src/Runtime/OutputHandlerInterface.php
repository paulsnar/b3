<?php declare(strict_types=1);
namespace PN\B3\Runtime;

interface OutputHandlerInterface
{
  public function output(string $message);
}
