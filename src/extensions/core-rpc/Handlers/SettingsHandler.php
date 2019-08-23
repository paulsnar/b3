<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreRpc\Handlers;
use PN\B3\Core\Site;

class SettingsHandler extends BaseHandler
{
  const METHOD_MAP = [
    'b3.getSettings' => 'getSettings',
  ];

  public function getSettings(array $params)
  {
    $this->checkAuth($params['auth_token'] ?? null);

    $settings = [ ];

    foreach (Site::getInstance()->getSettings() as $setting) {
      $settings[$setting->key] = [
        'value' => $setting->value,
        'name' => $setting->name,
        'description' => $setting->description,
      ];
    }

    return $settings;
  }

  public function updateSettings(array $params)
  {
    $this->checkAuth($params['auth_token'] ?? null);

    $changedSettings = [ ];
    foreach (Site::getInstance()->getSettings() as $setting) {
      if (array_key_exists($setting->key, $params)) {
        $setting->value = $params[$setting->key];
        $changedSettings[] = $setting;
      }
    }

    Site::getInstance()->updateSettings($changedSettings);
    return true;
  }
}
