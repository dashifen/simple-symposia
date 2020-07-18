<?php

namespace Dashifen\SimpleSymposia\Agents;

use Dashifen\SimpleSymposia\SimpleSymposia;
use Dashifen\ACFAgent\AbstractFieldGroupAgent;
use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Handlers\Plugins\PluginHandlerInterface;

class FieldGroupAgent extends AbstractFieldGroupAgent
{
  /**
   * FieldGroupAgent constructor.
   *
   * @param PluginHandlerInterface $handler
   */
  public function __construct(PluginHandlerInterface $handler)
  {
    parent::__construct($handler, $handler->getPluginDir() . '/assets/acf');
  }
  
  /**
   * initialize
   *
   * Adds a filter to turn off the importing of ACF fields by this plugin for
   * the time being.
   *
   * @throws HandlerException
   */
  public function initialize(): void
  {
    // we'll use WordPress's add_filter function here so that we can reference
    // the __return_false function as a string.  the addFilter method of our
    // Agents doesn't allow that at the moment.  then, we just call our the
    // parent initialize method and we're done here.
    
    add_filter('acf-agent-import', '__return_false');
    parent::initialize();
  }
  
  /**
   * shouldExport
   *
   * Returns true if this plugin should export the specified ACF field group's
   * JSON notation.
   *
   * @param string $acfName
   *
   * @return bool
   */
  protected function shouldExport(string $acfName): bool
  {
    // so that we don't have to rely on specific titles, we're instead going
    // to rely on field names.  if a group has at least one field prefixed with
    // simpsymp, then we want to export it.
    
    $groupId = $this->getFieldGroupId($acfName);
    foreach (acf_get_fields($groupId) as $field) {
      if (strpos($field['name'] ?? '', SimpleSymposia::PREFIX) !== false) {
        return true;
      }
    }
    
    return false;
  }
}
