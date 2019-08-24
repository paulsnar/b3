<?php declare(strict_types=1);
namespace PN\B3\Templating;
use PN\B3\Core\Site;
use PN\B3\Db;
use PN\B3\Db\Queryable;
use Twig\Loader\LoaderInterface;
use Twig\Source;

class TemplateLoader implements LoaderInterface
{
  protected $db, $siteId;

  public function __construct(Site $site, ?Queryable $db = null)
  {
    $this->db = $db ?: Db::getGlobal();
    $this->siteId = $site->id;
  }

  public function getAllTemplates(?string $type = null): array
  {
    $query = 'select id, site_id, type, name, modified_at from templates ' .
      'where site_id = :site_id';
    $params = [':site_id' => $this->siteId];
    if ($type !== null) {
      $query .= ' and type = :type';
      $params[':type'] = $type;
    }

    $templates = $this->db->select($query, $params);
    return array_map(function (array $template): Template {
      return new Template($template);
    }, $templates);
  }

  public function getSourceContext($name)
  {
    $content = $this->db->selectOne('select content from templates ' .
      'where site_id = :site_id and name = :name',
      [':site_id' => $this->siteId, ':name' => $name]);
    if ($content === null) {
      throw new \RuntimeException(
        "Template not found: {$name} for site {$this->sideId}");
    }
    return new Source($content['content'], $name);
  }

  public function getCacheKey($name)
  {
    $id = $this->db->selectOne('select id from templates where name = :name',
      [':name' => $name]);
    if ($id === null) {
      throw new \RuntimeException(
        "Template not found: {$name} for site {$this->sideId}");
    }

    return (string) $id['id'];
  }

  public function isFresh($name, $time)
  {
    // TODO
    return true;

    $id = intval($name, 10);
    $modified = $this->db->selectOne(
      'select modified_at from templates where id = :id',
      [':id' => $id]);
    if ($modified === null) {
      return false;
    }
    return $modified['modified_at'] < $time;
  }

  public function exists($name)
  {
    $ok = $this->db->selectOne('select 1 from templates where name = :name',
      [':name' => $name]);
    return $ok !== null;
  }
}
