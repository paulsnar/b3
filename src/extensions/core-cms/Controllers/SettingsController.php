<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreCms\Controllers;
use PN\B3\Rpc;
use PN\B3\Core\{Post, Site};
use PN\B3\Http\{Request, Response, Session};
use PN\B3\Ext\CoreCms\{CmsException, TemplateRenderer};
use PN\B3\Rpc\RpcException;
use function PN\B3\array_pluck;

class SettingsController extends BaseController
{
  public function settingsAction(Request $rq): Response
  {
    $siteId = $this->requireSiteId($rq);

    $rpc = Rpc::getInstance();
    $settings = $rpc->call('b3.getSettings',
      ['descriptions' => true, 'site_id' => $siteId],
      $rq->attributes['auth.user']);

    $ctx = ['site_id' => $siteId, 'settings' => $settings];

    if ($rq->method === 'GET') {
      return TemplateRenderer::renderResponse('settings.html', $ctx);
    }

    if ( ! $rq->attributes['csrf.passed']) {
      $ctx['error'] = 'csrf';
      return TemplateRenderer::renderResponse('settings.html', $ctx);
    }

    $updates = [ ];
    foreach ($settings as $key => &$setting) {
      if ($rq->form->has($key) && $rq->form[$key] !== $setting['value']) {
        $updates[$key] = $setting['value'] = $rq->form[$key];
      }
    }

    if ($updates === [ ]) {
      $ctx['updated'] = false;
      return TemplateRenderer::renderResponse('settings.html', $ctx);
    }

    try {
      $rpc->call('b3.updateSettings', $updates, $rq->attributes['auth.user']);
    } catch (RpcException $exc) {
      $ctx['error'] = $exc->getData();
      return TemplateRenderer::renderResponse('settings.html', $ctx);
    }

    $ctx['updated'] = true;
    return TemplateRenderer::renderResponse('settings.html', $ctx);
  }
}
