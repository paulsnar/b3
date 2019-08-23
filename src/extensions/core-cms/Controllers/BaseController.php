<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreCms\Controllers;
use PN\B3\Rpc;
use PN\B3\Controllers\BaseController as B3BaseController;
use PN\B3\Http\{Request, Response};
use PN\B3\Rpc\RpcException;
use PN\B3\Ext\CoreCms\TemplateRenderer;

abstract class BaseController extends B3BaseController
{
  protected function getGlobalMiddleware(): array
  {
    return array_merge(parent::getGlobalMiddleware(), ['b3.auth']);
  }

  public function dispatch(Request $rq, string $action): Response
  {
    try {
      return parent::dispatch($rq, $action);
    } catch (RpcException $exc) {
      return TemplateRenderer::renderResponse(
        'error.html', ['error' => $exc->getData()]);
    }
  }

  /** @throws RpcException */
  protected function callRpc(Request $rq, string $method, array $params)
  {
    if ($rq->attributes->has('auth.token')) {
      $params['auth_token'] = $rq->attributes['auth.token'];
    }

    return Rpc::getInstance()->call($method, $params);
  }
}
