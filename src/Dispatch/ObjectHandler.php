<?php declare(strict_types=1);
namespace PN\B3\Dispatch;
use PN\B3\Http\{Request, Response};

class ObjectHandler implements HandlerInterface
{
  protected $object, $method;

  public function __construct($object, string $method)
  {
    $this->object = $object;
    $this->method = $method;
  }

  public function handle(Request $rq): Response
  {
    $object = $this->object;
    if (is_string($object)) {
      if (method_exists($object, 'getInstance')) {
        $object = $this->object = $object::getInstance();
      } else {
        $object = $this->object = new $object();
      }
    }
    $method = $this->method;

    return $object->$method($rq);
  }
}
