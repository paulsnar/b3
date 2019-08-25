<?php declare(strict_types=1);
namespace PN\B3\Rpc\CoreHandlers;
use PN\B3\App;
use PN\B3\Core\{Site, User};
use PN\B3\Rpc\RpcException;
use PN\B3\Templating\Template;
use function PN\B3\{array_index, array_pluck};

class TemplatesHandler
{
  public function listTemplates(array $params, User $user): array
  {
    $site = $params['site_id'] ?? null;
    if ($site === null) {
      throw RpcException::invalidParams('No `site_id` specified.');
    }

    $type = $params['type'] ?? null;
    if ($type !== null && ! Template::isValidType($type)) {
      throw RpcException::invalidParams('Invalid `type` specified.');
    }

    $query = ['site_id' => $site];
    if ($type !== null) {
      $query['type'] = $type;
    }
    return Template::select($query);
  }

  public function getTemplate(array $params, User $user): Template
  {
    $id = $params['id'] ?? null;
    if ($id === null) {
      throw RpcException::invalidParams('No `id` specified.');
    }

    $template = Template::lookup(['id' => $id]);
    if ($template === null) {
      throw new RpcException(1003, 'Not found',
        'The provided criteria did not match any items.');
    }

    return $template;
  }

  public function newTemplate(array $params, User $user): Template
  {
    foreach (['site_id', 'name', 'content'] as $param) {
      if (($params[$param] ?? null) === null) {
        throw RpcException::invalidParams("Parameter `{$param}` not present.");
      }
    }

    if ( ! Site::exists(['id' => $params['site_id']])) {
      throw RpcException::invalidParams(
        '`site_id` does not correspond to a known site.');
    }

    if (array_key_exists('type', $params)) {
      $type = $params['type'];
      if ( ! Template::isValidType($type)) {
        throw RpcException::invalidParams(
          "'{$type}' is not a valid template type.");
      }
    } else {
      $params['type'] = Template::TYPE_AMBIENT;
    }

    $template = array_pluck($params, 'site_id', 'type', 'name', 'content');
    $template['modified_at'] = time();
    $template = Template::insert($template);

    App::getInstance()->dispatchEvent('b3.templates.new', $template);
    return $template;
  }

  public function editTemplate(array $params, User $user): Template
  {
    $id = $params['template_id'] ?? null;
    if ($id === null) {
      throw RpcException::invalidParams('`template_id` not present.');
    }

    $template = Template::lookup(['id' => $id]);
    if ($template === null) {
      throw new RpcException(1003, 'Not found',
        'The criteria provided do not correspond to an item.');
    }

    $update = array_pluck($params, 'site_id', 'type', 'name', 'content');
    if (array_key_exists('type', $update) &&
        ! Template::isValidType($update['type'])) {
      throw RpcException::invalidParams(
        "'{$type}' is not a valid template type.");
    }

    $template->update($update);

    App::getInstance()->dispatchEvent('b3.templates.edited', $template);
    return $template;
  }

  public function deleteTemplate(array $params, User $user): bool
  {
    $id = $params['template_id'] ?? null;
    if ($id === null) {
      throw RpcException::invalidParams('`template_id` not present.');
    }

    $template = Template::lookup(['id' => $id]);
    if ($template === null) {
      throw new RpcException(1003, 'Not found',
        'The criteria provided do not correspond to an item.');
    }

    $template->delete();

    App::getInstance()->dispatchEvent('b3.templates.deleted', $template);
    return true;
  }
}
