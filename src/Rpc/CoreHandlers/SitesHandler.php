<?php declare(strict_types=1);
namespace PN\B3\Rpc\CoreHandlers;
use PN\B3\Core\{Site, User};
use PN\B3\Rpc\RpcException;
use function PN\B3\array_pluck;

class SitesHandler
{
  public function listSites(array $params, User $user): array
  {
    return Site::select([ ]);
  }

  public function getSite(array $params, User $user): Site
  {
    $id = $params['id'] ?? null;
    if ($id === null) {
      throw RpcException::invalidParams(
        'No criterion provided for site lookup.');
    }

    $site = Site::lookup(['id' => $id]);
    if ($site === null) {
      throw new RpcException(1003, 'Item not found',
        'The criteria provided do not match any items.');
    }

    return $site;
  }

  public function newSite(array $params, User $user): Site
  {
    $title = $params['title'] ?? null;
    $baseUrl = $params['base_url'] ?? null;
    $targetPath = $params['target_path'] ?? null;

    if ($title === null || $baseUrl === null || $targetPath === null) {
      throw RpcException::invalidParams(
        'All of `title`, `base_url`, `target_path` must be provided.');
    }

    // TODO: dispatch event

    return Site::insert([
      'title' => $title,
      'base_url' => $baseUrl,
      'target_path' => $targetPath,
    ]);
  }

  public function editSite(array $params, User $user): Site
  {
    $id = $params['site_id'] ?? null;
    if ($id === null) {
      throw RpcException::invalidParams(
        'Parameter `site_id` must be provided.');
    }

    $site = Site::lookup(['id' => $id]);
    if ($site === null) {
      throw new RpcException(1003, 'Not found',
        'The criteria provided do not match any items.');
    }

    $updates = array_pluck($params, 'title', 'base_url', 'target_path');
    $site->update($updates);

    // TODO: dispatch event

    return $site;
  }
}
