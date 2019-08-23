<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreCms\Controllers;
use PN\B3\Rpc;
use PN\B3\Core\{Post, Site};
use PN\B3\Controllers\BaseController;
use PN\B3\Http\{Request, Response, Session};
use PN\B3\Ext\CoreCms\TemplateRenderer;
use PN\B3\Rpc\RpcException;
use function PN\B3\array_pluck;

class SettingsController extends BaseController
{
  public function settingsAction(Request $rq): Response
  {
    $rpc = Rpc::getInstance();

    $settings = $rpc->call('b3.getSettings', [
      'auth_token' => $rq->attributes['auth.token'],
      'descriptions' => true,
    ]);

    if ($rq->method === 'GET') {
      return TemplateRenderer::renderResponse(
        'settings.html', compact('settings'));
    }

    if ( ! $rq->attributes['csrf.passed']) {
      $error = 'csrf';
      return TemplateRenderer::renderResponse(
        'settings.html', compact('settings', 'error'));
    }

    $updates = [ ];
    foreach ($settings as $key => $setting) {
      if ($rq->form->has($key) && $rq->form[$key] !== $setting->value) {
        $updates[$key] = $setting->value = $rq->form[$key];
      }
    }

    if ($updates === [ ]) {
      $updated = false;
      return TemplateRenderer::renderResponse(
        'settings.html', compact('settings', 'updated'));
    }

    try {
      $rpc->call('b3.updateSettings', [
        'auth_token' => $rq->attributes['auth.token'],
        'settings' => $updates,
      ]);
    } catch (RpcException $exc) {
      $error = $exc->getData();
      return TemplateRenderer::renderResponse(
        'settings.html', compact('settings', 'error'));
    }

    $updated = true;
    return TemplateRenderer::renderResponse(
      'settings.html', compact('settings', 'updated'));
  }
}
