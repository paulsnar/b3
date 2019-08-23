<?php declare(strict_types=1);
namespace PN\B3\Core;
use PN\B3\Models\BaseModel;

class User extends BaseModel
{
  protected const TABLE = 'users';

  public $username, $password;
}
