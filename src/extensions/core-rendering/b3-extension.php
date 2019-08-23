<?php declare(strict_types=1);
namespace PN\B3\Ext\CoreRendering;
use PN\B3\Extension;
use const PN\B3\B3_VERSION;

new class extends Extension {
  public function __construct()
  {
    parent::__construct([
      'id' => 'b3/core-rendering',
      'description' => 'Core: Renders posts to static files',
      'author' => 'b3 <b3@pn.id.lv>',
      'version' => B3_VERSION,
    ]);

    $this->addEventListener('b3-ext.boot', function () {
      Renderer::getInstance();
    });
  }
};
