<?php declare(strict_types=1);
namespace PN\B3\Core;

class Author
{
  public $name, $url, $avatar, $email;

  public static function fromGlobals()
  {
    static $author;
    if ($author !== null) {
      return $author;
    }

    $author = new static();
    if (defined('PN\\B3\\SITE_AUTHOR_NAME')) {
      $author->name = constant('PN\\B3\\SITE_AUTHOR_NAME');
    }
    if (defined('PN\\B3\\SITE_AUTHOR_URL')) {
      $author->url = constant('PN\\B3\\SITE_AUTHOR_URL');
    }
    if (defined('PN\\B3\\SITE_AUTHOR_AVATAR')) {
      $author->avatar = constant('PN\\B3\\SITE_AUTHOR_AVATAR');
    }
    if (defined('PN\\B3\\SITE_AUTHOR_EMAIL')) {
      $author->email = constant('PN\\B3\\SITE_AUTHOR_EMAIL');
    }
    return $author;
  }

  public function toArray()
  {
    $author = [ ];
    if ($this->name !== null) {
      $author['name'] = $this->name;
    }
    if ($this->url !== null) {
      $author['url'] = $this->url;
    }
    if ($this->avatar !== null) {
      $author['avatar'] = $this->author;
    }
    if ($this->email !== null) {
      if ($this->url === null) {
        $author['url'] = 'mailto:' . $this->email;
      } else {
        $author['_email'] = $this->email;
      }
    }
    return $author;
  }
}
