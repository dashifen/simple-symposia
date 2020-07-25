<?php

namespace Dashifen\SimpleSymposia\Agents\PostTypesAndTaxes;

use Dashifen\SimpleSymposia\SimpleSymposia;
use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\SimpleSymposia\Agents\PostTypeAgent;
use Dashifen\WPHandler\Agents\AbstractPluginAgent;
use Dashifen\WPHandler\Traits\OptionsManagementTrait;

class SessionAgent extends AbstractPluginAgent
{
  /** @var SimpleSymposia $handler */
  protected $handler;
  
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
  
  /**
   * addTrackNames
   *
   * Gets the list of tracks for the current symposium and makes them the
   * choices for the field we're filtering.
   *
   * @param array $field
   *
   * @return array
   * @throws HandlerException
   */
  protected function addTrackNames(array $field): array
  {
    $symposiumAgent = $this->handler->getSymposiumAgent();
    $field['choices'] = $symposiumAgent->getCurrentSymposiumTracks();
    return $field;
  }
}
