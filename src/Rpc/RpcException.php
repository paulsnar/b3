<?php declare(strict_types=1);
namespace PN\B3\Rpc;

class RpcException extends \Exception
{
  protected $data;

  public function __construct(int $code, string $message, $data = null)
  {
    parent::__construct($message, $code);
    $this->data = $data;
  }

  public static function invalidParams($data)
  {
    return new static(-32602, 'Invalid params', $data);
  }

  public function getData()
  {
    return $this->data;
  }

  public function toArray(): array
  {
    $array = [
      'code' => $this->code,
      'message' => $this->message,
      'data' => $this->data,
    ];
    if ($this->data !== null) {
      $array['data'] = $this->data;
    }
    return $array;
  }
}
