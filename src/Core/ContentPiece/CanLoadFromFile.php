<?php declare(strict_types=1);
namespace PN\B3\Core\ContentPiece;
use function PN\B3\str_starts_with;

trait CanLoadFromFile
{
  protected static function parseMetadata(string $metadataString)
  {
    $lines = explode("\n", $metadataString);

    $pairs = array_map(function ($pair) {
      if ($pair === '') {
        return null;
      }
      if (strpos($pair, '=') === false) {
        throw new \RuntimeException('Malformed metadata pair ' .
          '(no equals sign): ' . $pair);
      }
      $pair = explode('=', $pair, 2);
      return array_map('trim', $pair);
    }, $lines);

    $pairs = array_filter($pairs, function ($pair) {
      return $pair !== null;
    });

    return array_reduce($pairs, function ($metadata, $pair) {
      [$name, $value] = $pair;
      $metadata[$name] = json_decode($value, true);
      return $metadata;
    }, [ ]);
  }

  public static function fromFile(string $path)
  {
    $content = file_get_contents($path);
    $metadata = [
      'file_extension' => substr($path, strrpos($path, '.') + 1),
      'file_path' => $path,
    ];

    if (str_starts_with($content, "---\n")) {
      $metadataStart = 4;
      $metadataEnd = strpos($content, "\n---\n", 4);
      if ($metadataEnd !== false) {
        $metadataStr = substr($content,
          $metadataStart, $metadataEnd - $metadataStart);
        $metadata = $metadata + static::parseMetadata($metadataStr);
        $content = substr($content, $metadataEnd + 5);
      }
    }

    return new static($content, $metadata);
  }
}
