<?php

namespace Dashifen\SimpleSymposia\Agents;

use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Agents\AbstractPluginAgent;

class SessionAgent extends AbstractPluginAgent
{
  /**
   * initialize
   *
   * Hooks protected methods of this object into the WordPress ecosystem.
   *
   * @return void
   * @throws HandlerException
   */
  public function initialize(): void
  {
    if (!$this->isInitialized()) {
      $this->addFilter('acf/load_field/key=field_5f14d6ed7fc13', 'addTrackNames');
    }
  }
  
  protected function addTrackNames(array $field): array
  {
    self::debug($field, true);
    
    return $field;
  }
}
