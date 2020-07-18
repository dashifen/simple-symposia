<?php

namespace Dashifen\SimpleSymposia;

use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Handlers\Plugins\AbstractPluginHandler;
use Dashifen\SimpleSymposia\Agents\PostTypeAgent;

class SimpleSymposia extends AbstractPluginHandler
{
  public const PREFIX = 'simpsymp-';
  
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
    if (!$this->isInitialized()) {
      $this->registerActivationHook('activate');
      
      // we initialize agents at priority level 5 so that the agents themselves
      // can use the default of 10 without it being already in progress.
      
      $this->addAction('init', 'initializeAgents', 5);
      $this->addAction('init', 'activate');
    }
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
    $contentStructureRegistrationAgent = $agents[PostTypeAgent::class];
    $contentStructureRegistrationAgent->register();
    flush_rewrite_rules();
  }
}
