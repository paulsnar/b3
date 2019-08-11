<?php declare(strict_types=1);
namespace PN\B3\Runtime\Warnings;
use PN\B3\Runtime\{OutputHandlerInterface, StderrOutputHandler};

class WarningManager
{
  public static function getGlobalInstance()
  {
    static $instance;
    if ($instance === null) {
      $instance = new static();
    }
    return $instance;
  }

  protected $outputHandler;

  public function __construct(?OutputHandlerInterface $outputHandler = null)
  {
    $this->outputHandler = new StderrOutputHandler();
  }

  public function setOutputHandler(OutputHandlerInterface $outputHandler)
  {
    $this->outputHandler = $outputHandler;
  }

  public function printWarning(Warning $warning)
  {
    if ($this->outputHandler instanceof WarningCollectorInterface) {
      $this->outputHandler->collectWarning($warning);
    } else {
      $call = $warning->call;
      if (array_key_exists('class', $call)) {
        $call = $call['class'] . $call['type'] . $call['function'];
      } else {
        $call = $call['function'];
      }
      $message = sprintf("[b3] Warning: %s\n     in %s\n       at %s:%d",
        $warning->message, $call, $warning->file, $warning->line);
      $this->outputHandler->output($message . PHP_EOL);
    }
  }
}
