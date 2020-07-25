<?php

namespace Dashifen\SimpleSymposia;

use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\SimpleSymposia\Agents\PostTypeAgent;
use Dashifen\WPHandler\Handlers\Plugins\AbstractPluginHandler;
use Dashifen\SimpleSymposia\Agents\Collection\AgentCollection;
use Dashifen\SimpleSymposia\Agents\PostTypesAndTaxes\SymposiumAgent;

class SimpleSymposia extends AbstractPluginHandler
{
  public const PREFIX = 'simpsymp-';
  
  /**
   * @var AgentCollection
   */
  protected $agentCollection;
  
  /**
   * getSymposiumAgent
   *
   * Returns the symposium agent that is a part of this handler's agent
   * collection.
   *
   * @return SymposiumAgent
   */
  public function getSymposiumAgent(): SymposiumAgent
  {
    return $this->agentCollection->getSymposiumAgent();
  }
  
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
      $this->addAction('init', 'activate');
      
      // we initialize agents at priority level 5 so that the agents themselves
      // can use the default of 10 without it being already in progress.
      
      $this->addAction('init', 'initializeAgents', 5);
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
    $this->agentCollection->getPostTypeAgent()->register();
    
    // this method is called both on activation and during the init action.
    // only during activation do we want to flush the rewrite rules, so we can
    // see if this is currently init to determine if we should do so or not.
    
    if (current_action() !== 'init') {
      flush_rewrite_rules();
    }
  }
}
