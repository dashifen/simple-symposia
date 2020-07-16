<?php

namespace Dashifen\SimpleSymposia;

use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Handlers\Plugins\AbstractPluginHandler;
use Dashifen\SimpleSymposia\Agents\ContentStructureRegistrationAgent;

class SimpleSymposia extends AbstractPluginHandler
{
  /**
   * initialize
   *
   * Connects the methods of this object into the WordPress ecosystem.
   *
   * @return void
   * @throws HandlerException
   */
  public function initialize(): void
  {
    $this->registerActivationHook('activate');
    $this->addAction('init', 'registerContentStructures');
  }
  
  /**
   * activate
   *
   * Activates the plugin by registering the content structures and then
   * flushing the permalinks.
   *
   * @return void
   */
  protected function activate(): void
  {
    $agents = $this->getAgentCollection();
    $contentStructureRegistrationAgent = $agents[ContentStructureRegistrationAgent::class];
    $contentStructureRegistrationAgent->registerContentStructures();
    flush_rewrite_rules();
  }
}
