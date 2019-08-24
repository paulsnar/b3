<?php declare(strict_types=1);
namespace PN\B3\Core;
use PN\B3\Db\DbObject;
use function PN\B3\array_without;

class User extends DbObject
{
  protected const TABLE = 'users';
  protected const COLUMNS = [
    'username' => 'string',
    'password' => 'string',
  ];

  public function serializeJson()
  {
    return array_without(parent::serializeJson(), 'password');
  }
}
