<?php declare(strict_types=1);
namespace PN\B3\Rpc\CoreHandlers;
use PN\B3\Core\{Site, User};

class SettingsHandler
{
  public function getSettings(array $params, User $user): array
  {
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

  public function updateSettings(array $params, User $user): bool
  {
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
