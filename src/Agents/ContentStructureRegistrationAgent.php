<?php

namespace Dashifen\SimpleSymposia\Agents;

use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Agents\AbstractPluginAgent;

class ContentStructureRegistrationAgent extends AbstractPluginAgent
{
  /**
   * initialize
   *
   * Hooks the methods of this object into the WordPress ecosystem.
   *
   * @return void
   * @throws HandlerException
   */
  public function initialize(): void
  {
    $this->addAction('init', 'registerContentStructures');
  }
  
  /**
   * registerContentStructures
   *
   * Registers the content structures (i.e. post types and taxonomies) we need
   */
  public function registerContentStructures(): void
  {
    // typically, Dash tends to use protected methods for their action and
    // filter callbacks.  but, this method needs to be executed both via the
    // init action and also when the plugin is activated.  therefore, it needs
    // to be public so that the SimpleSymposia object can reference it.
    
    $this->registerEventPostType();
    $this->registerSymposiumTaxonomy();
  }
  
  private function registerEventPostType(): void
  {
  
  }
  
  private function registerSymposiumTaxonomy(): void
  {
  
  }
}
