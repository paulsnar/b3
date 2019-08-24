<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreCms\Controllers;
use PN\B3\Controllers\BaseController as B3BaseController;
use PN\B3\Core\Site;
use PN\B3\Ext\CoreCms\{CmsException, TemplateRenderer};
use PN\B3\Http\{HttpSerializable, Request, Response};
use PN\B3\Rpc;
use PN\B3\Rpc\RpcException;

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
    } catch (CmsException $exc) {
      return TemplateRenderer::renderResponse('error.html', [
        'error' => $exc->getMessage(),
        'site_id' => $rq->query['site_id'],
      ]);
    } catch (HttpSerializable $exc) {
      throw $exc;
    } catch (\Throwable $exc) {
      // return pretty error message
      $trace = '';
      foreach ($exc->getTrace() as $line => $traceItem) {
        if ($traceItem['class'] ?? false) {
          $call = $traceItem['class'] . $traceItem['type'] .
            $traceItem['function'];
        } else {
          $call = $traceItem['function'];
        }
        $trace .= sprintf("%2d: %s (%s:%s)\n", $line, $call,
          $traceItem['file'] ?? '<none>', $traceItem['line'] ?? 0);
      }

      return TemplateRenderer::renderResponse('exception.html', [
        'exception' => $exc,
        'exception_class' => get_class($exc),
        'exception_trace' => $trace,
        'site_id' => $rq->query['site_id'],
      ]);
    }
  }

  protected function requireSiteId(
    Request $rq,
    bool $verify = true
  ): int {
    $id = $rq->query['site_id'];
    if ($id === null || ! ctype_digit($id)) {
      throw new CmsException('missing_site_id');
    }

    $id = intval($id, 10);
    if ($verify && ! Site::exists(['id' => $id])) {
      throw new CmsException('invalid_site_id');
    }

    return $id;
  }
}
