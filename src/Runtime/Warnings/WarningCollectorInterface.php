<?php declare(strict_types=1);
namespace PN\B3\Runtime\Warnings;

interface WarningCollectorInterface
{
  public function collectWarning(Warning $warning);
}
