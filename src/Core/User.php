<?php declare(strict_types=1);
namespace PN\B3\Core;
use PN\B3\Models\BaseModel;
use function PN\B3\array_without;

class User extends BaseModel
{
  protected const TABLE = 'users';

  public $username, $password;

  public function serializeJson()
  {
    return array_without(parent::serializeJson(), 'password');
  }
}
