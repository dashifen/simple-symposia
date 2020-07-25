<?php

namespace Dashifen\SimpleSymposia\Agents\Collection;

use Dashifen\SimpleSymposia\Agents\PostTypeAgent;
use Dashifen\SimpleSymposia\Agents\PostTypesAndTaxes\SymposiumAgent;
use Dashifen\WPHandler\Agents\Collection\AgentCollection as BaselineAgentCollection;

class AgentCollection extends BaselineAgentCollection
{
  /**
   * getPostTypeAgent
   *
   * A convenience method to access the PostTypeAgent that is a part of this
   * collection.
   *
   * @return PostTypeAgent
   */
  public function getPostTypeAgent(): PostTypeAgent
  {
    /** @var PostTypeAgent $postTypeAgent */
    $postTypeAgent = $this->collection[PostTypeAgent::class];
    return $postTypeAgent;
  }
  
  /**
   * getSymposiumAgent
   *
   * A convenience method to access the SymposiumAgent that is a part of this
   * collection.
   *
   * @return SymposiumAgent
   */
  public function getSymposiumAgent(): SymposiumAgent
  {
    /** @var SymposiumAgent $symposiumAgent */
    $symposiumAgent = $this->collection[SymposiumAgent::class];
    return $symposiumAgent;
  }
}
