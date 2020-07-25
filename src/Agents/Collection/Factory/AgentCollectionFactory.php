<?php

namespace Dashifen\SimpleSymposia\Agents\Collection\Factory;

use Dashifen\Repository\RepositoryException;
use Dashifen\SimpleSymposia\Agents\PostTypeAgent;
use Dashifen\SimpleSymposia\Agents\FieldGroupAgent;
use Dashifen\SimpleSymposia\Agents\PostTypesAndTaxes\SessionAgent;
use Dashifen\WPHandler\Agents\Collection\AgentCollectionInterface;
use Dashifen\SimpleSymposia\Agents\PostTypesAndTaxes\SymposiumAgent;
use Dashifen\WPHandler\Agents\Collection\Factory\AgentCollectionFactory as BaselineAgentCollectionFactory;
use Dashifen\SimpleSymposia\Agents\Collection\AgentCollection as SimpSympAgentCollection;

/**
 * Class AgentCollectionFactory
 *
 * @package Dashifen\SimpleSymposia\Agents\Collection\Factory
 */
class AgentCollectionFactory extends BaselineAgentCollectionFactory
{
  /**
   * AgentCollectionFactory constructor.
   *
   * @throws RepositoryException
   */
  public function __construct()
  {
    $this->registerAgent(PostTypeAgent::class);
    $this->registerAgent(FieldGroupAgent::class);
    $this->registerAgent(SymposiumAgent::class);
    $this->registerAgent(SessionAgent::class);
  }
  
  /**
   * produceAgentCollectionInstance
   *
   * Overrides our parent's method to return a more context-sensitive version
   * of our agent collection interface.
   *
   * @return AgentCollectionInterface
   */
  protected function produceAgentCollectionInstance(): AgentCollectionInterface
  {
    return new SimpSympAgentCollection();
  }
}
