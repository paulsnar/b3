<?php declare(strict_types=1);
namespace PN\B3\Http;

class StaticFileResponse extends Response
{
  protected $file;

  public function __construct(
    Request $rq,
    string $filename,
    ?string $mimetype = 'application/octet-stream'
  ) {
    $stat = stat($filename);
    $mtime = $stat['mtime'];
    $mtime = new \DateTimeImmutable('@' . $mtime, new \DateTimeZone('UTC'));
    $mtime = $mtime->format('D, d M Y H:i:s') . ' GMT';

    if ($rq->headers['If-Modified-Since'] === $mtime) {
      parent::__construct(Status::NOT_MODIFIED);
      return;
    }

    parent::__construct(Status::OK, [
      'Last-Modified' => $mtime,
      'Content-Type' => $mimetype,
    ], null);
    $this->file = fopen($filename, 'r');
  }

  public function __destruct()
  {
    if ($this->file !== null) {
      fclose($this->file);
      $this->file = null;
    }
  }

  public function send()
  {
    parent::send();
    if ($this->file !== null) {
      fpassthru($this->file);
    }
  }
}
