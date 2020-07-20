<?php

namespace Dashifen\SimpleSymposia;

use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\SimpleSymposia\Agents\SymposiumAgent;
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
      $this->addAction('init', 'activate');
      
      // we initialize agents at priority level 5 so that the agents themselves
      // can use the default of 10 without it being already in progress.
      
      $this->addAction('init', 'initializeAgents', 5);
      $this->addAction('admin_enqueue_scripts', 'addAssets');
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
    
    // this method is called both on activation and during the init action.
    // only during activation do we want to flush the rewrite rules, so we can
    // see if this is currently init to determine if we should do so or not.
    
    if (current_action() !== 'init') {
      flush_rewrite_rules();
    }
  }
  
  /**
   * addAssets
   *
   * Adds CSS and JS assets necessary for this plugin.
   *
   * @return void
   */
  protected function addAssets(): void
  {
    if (SymposiumAgent::isSymposiumEditor()) {
      $this->enqueue('assets/css/symposium-styles.css');
    }
  }
}
