<?php declare(strict_types=1);
namespace PN\B3;
use PN\B3\Core\User;

require dirname(__DIR__) . '/vendor/autoload.php';

function main(): int {
  $stdin = fopen('php://stdin', 'r');

  $username = $_SERVER['argv'][1] ?? null;
  if ($username === null) {
    echo "Username: ";
    $username = rtrim(fgets($stdin), "\r\n");
  }

  try {
    exec('stty -echo');
    echo "Password: ";
    $password = rtrim(fgets($stdin), "\r\n");
    echo "\nPassword again: ";
    $password2 = rtrim(fgets($stdin), "\r\n");
    echo "\n";
  } finally {
    exec('stty echo');
  }

  if ($password !== $password2) {
    echo "Passwords don't match.\n";
    return 1;
  }

  if (User::lookup(['username' => $username]) !== null) {
    echo "That username is already taken.\n";
    return 1;
  }

  $user = User::insert([
    'username' => $username,
    'password' => password_hash($password, PASSWORD_DEFAULT),
  ]);
  echo "ok: user id {$user->id}\n";
  return 0;
}

exit(main());
var_dump($_SERVER);
