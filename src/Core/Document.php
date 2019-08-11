<?php declare(strict_types=1);
namespace PN\B3\Core;

/**
 * Document represents a standalone page that can be rendered into a static
 * file.
 */
class Document
{
  public $metadata = [ ], $content;

  public function __construct(string $content, array $metadata = [ ])
  {
    $this->metadata = $metadata;
    $this->content = $content;
  }

  public static function fromFile(string $path)
  {
    $content = file_get_contents($path);

    if (substr($content, 0, 3) === '---') {
      $metadataEnd = strpos($content, '---', 3);
      if ($metadataEnd !== false) {
        $metadata = substr($content, 3, $metadataEnd - 3);
        $metadata = static::parseMetadata($metadata);
        $content = substr($content, $metadataEnd + 3);
      }
    } else {
      $metadata = [ ];
    }

    $metadata['file_path'] = $path;

    return new static($content, $metadata);
  }

  protected static function parseMetadata(string $metadataString)
  {
    $lines = explode("\n", $metadataString);
    $pairs = array_map(function ($pair) {
      if ($pair === '') {
        return null;
      }
      if (strpos($pair, '=') === false) {
        throw new \RuntimeException("Invalid metadata keyvalue pair: {$pair}");
      }
      $pair = explode('=', $pair, 2);
      return array_map('trim', $pair);
    }, $lines);
    $pairs = array_filter($pairs, function ($pair) {
      return $pair !== null;
    });
    return array_reduce($pairs, function ($carry, $pair) {
      [$name, $value] = $pair;
      $carry[$name] = $value;
      return $carry;
    }, [ ]);
  }
}
