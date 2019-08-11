<?php declare(strict_types=1);
namespace PN\B3\Runtime;

class StderrOutputHandler extends AbstractStreamOutputHandler
{
  protected function openStream()
  {
    return fopen('php://stderr', 'w');
  }
}
