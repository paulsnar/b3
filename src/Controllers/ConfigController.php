<?php declare(strict_types=1);
namespace PN\B3\Controllers;
use PN\B3\{Config, Site};
use PN\B3\Http\{Request, Response, Status};
use PN\B3\Models\User;

class ConfigController extends BaseController
{
  public function needsAuthentication(Request $rq, string $action): bool
  {
    if ($action === 'install') {
      return false;
    }
    return true;
  }

  public function installAction(Request $rq): Response
  {
    $db = Config::getDb();

    $userExists = $db->selectOne('select 1 as exists from users limit 1');
    if ($userExists !== null) {
      return $this->renderTemplateResponse('config/install.html',
        ['error' => 'installed', 'done' => true], Status::FORBIDDEN);
    }

    if ($rq->method === 'GET') {
      return $this->renderTemplateResponse('config/install.html');
    }

    if ( ! $this->checkCsrfToken($rq->form['_csrf'])) {
      return $this->renderTemplateResponse('config/install.html',
        ['error' => 'csrf']);
    }

    $password = bin2hex(random_bytes(16));
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $user = User::insert($db, [
      'username' => 'root',
      'password' => $passwordHash,
    ]);

    return $this->renderTemplateResponse('config/install.html', [
      'done' => true,
      'user' => $user,
      'password' => $password,
    ]);
  }

  public function settingsAction(Request $rq): Response
  {
    $settings = Site::getInstance()->getSettings();

    if ($rq->method === 'GET') {
      return $this->renderTemplateResponse('config/settings.html',
        compact('settings'));
    }

    if ( ! $this->checkCsrfToken($rq->form['_csrf'])) {
      $error = 'csrf';
      return $this->renderTemplateResponse('config/settings.html',
        compact('settings', 'error'));
    }

    $updates = [ ];
    foreach ($settings as $key => $setting) {
      if ($rq->form->has($key) && $rq->form[$key] !== $setting->value) {
        $setting->value = $rq->form[$key];
        $updates[] = $setting;
      }
    }

    if ($updates === [ ]) {
      $success = null;
    } else {
      Site::getInstance()->updateSettings($updates);
      $success = true;
    }

    return $this->renderTemplateResponse('config/settings.html',
      compact('settings', 'success'));
  }
}
