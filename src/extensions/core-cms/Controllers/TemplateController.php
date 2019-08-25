<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreCms\Controllers;
use PN\B3\Ext\CoreCms\{CmsException, TemplateRenderer};
use PN\B3\Http\{Request, Response, Session};
use PN\B3\Rpc;
use PN\B3\Templating\Template;
use function PN\B3\{obj_pluck, url_join};

class TemplateController extends BaseController
{
  public function templatesAction(Request $rq): Response
  {
    $site = $this->requireSiteId($rq);

    $templates = Rpc::getInstance()->call('b3.listTemplates', [
      'site_id' => $site,
    ], $rq->attributes['auth.user']);
    return TemplateRenderer::renderResponse('templates.html',
      ['site_id' => $site, 'templates' => $templates]);
  }

  public function templatesNewAction(Request $rq): Response
  {
    $site = $this->requireSiteId($rq);
    $ctx = ['site_id' => $site, 'template' => null];

    if ($rq->method === 'GET') {
      return TemplateRenderer::renderResponse('edit_template.html', $ctx);
    }

    $template = ['site_id' => $site] + $rq->form->pluck('name', 'content');
    $ctx['template'] =& $template;
    if ($rq->form->has('type') && Template::isValidType($rq->form['type'])) {
      $template['type'] = $rq->form['type'];
    }

    if ( ! $rq->attributes['csrf.passed']) {
      $ctx['error'] = 'csrf';
      return TemplateRenderer::renderResponse('edit_template.html', $ctx);
    }

    try {
      $template = Rpc::getInstance()->call(
        'b3.newTemplate', $template, $rq->attributes['auth.user']);
      $ctx['template'] = $template;
    } catch (RpcException $exc) {
      $ctx['error'] = $exc->getData();
      return TemplateRenderer::renderResponse('edit_template.html', $ctx);
    }

    Session::setFlash('new_template', true);
    return Response::redirectTo('?templates/edit&id=' . $template->id);
  }

  public function templatesEditAction(Request $rq): Response
  {
    if ( ! $rq->query->has('id')) {
      throw new CmsException('missing_id');
    }

    $id = intval($rq->query['id'], 10);
    $template = Rpc::getInstance()->call('b3.getTemplate', ['id' => $id],
      $rq->attributes['auth.user']);
    $site = Rpc::getInstance()->call('b3.getSite', ['id' => $template->site_id],
      $rq->attributes['auth.user']);

    $ctx = [
      'template' => $template,
      'site_id' => $site->id,
      'template_url' => url_join($site->base_url, $template->name),
    ];

    if ($rq->method === 'GET') {
      return TemplateRenderer::renderResponse('edit_template.html', $ctx);
    }

    $template->name = $rq->form['name'];
    $template->content = $rq->form['content'];
    if (Template::isValidType($rq->form['type'])) {
      $template->type = $rq->form['type'];
    }

    if ( ! $rq->attributes['csrf.passed']) {
      $ctx['error'] = 'csrf';
      return TemplateRenderer::renderResponse('edit_template.html', $ctx);
    }

    try {
      $update = obj_pluck($template, 'name', 'content', 'type');
      $update['template_id'] = $template->id;
      Rpc::getInstance()->call('b3.editTemplate', $update,
        $rq->attributes['auth.user']);
    } catch (RpcException $exc) {
      $ctx['error'] = $exc->getData();
      return TemplateRenderer::renderResponse('edit_template.html', $ctx);
    }

    $ctx['updated'] = true;
    return TemplateRenderer::renderResponse('edit_template.html', $ctx);
  }

  public function templatesDeleteAction(Request $rq): Response
  {
    if ( ! $rq->query->has('id')) {
      throw new CmsException('missing_id');
    }

    $id = intval($rq->query['id'], 10);
    $template = Rpc::getInstance()->call('b3.getTemplate', ['id' => $id],
      $rq->attributes['auth.user']);

    $ctx = ['template' => $template, 'site_id' => $template->site_id];

    if ($rq->method === 'GET') {
      return TemplateRenderer::renderResponse('delete_template.html', $ctx);
    }

    if ( ! $rq->attributes['csrf.passed']) {
      $ctx['error'] = 'csrf';
      return TemplateRenderer::renderResponse('delete_template.html', $ctx);
    }

    try {
      Rpc::getInstance()->call('b3.deleteTemplate',
        ['template_id' => $template->id], $rq->attributes['auth.user']);
    } catch (RpcException $exc) {
      $ctx['error'] = $exc->getData();
      return TemplateRenderer::renderResponse('delete_template.html', $ctx);
    }

    Session::setFlash('deleted_template', obj_pluck($template, 'name'));
    return Response::redirectTo('?templates&site_id=' . $template->site_id);
  }
}
