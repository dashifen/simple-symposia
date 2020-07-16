<?php

namespace Dashifen\SimpleSymposia\Agents;

use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Agents\AbstractPluginAgent;
use Dashifen\WPHandler\Traits\PostTypeRegistrationTrait;
use Dashifen\WPHandler\Traits\TaxonomyRegistrationTrait;

class PostTypeAgent extends AbstractPluginAgent
{
  use PostTypeRegistrationTrait;
  use TaxonomyRegistrationTrait;
  
  public const POST_TYPE = 'simpsymp-session';
  public const TAXONOMY = 'simpsymp-symposium';
  
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
    $this->addAction('init', 'register');
  }
  
  /**
   * register
   *
   * Registers the session post type as well as associated taxonomies.
   *
   * @return void
   */
  public function register(): void
  {
    // typically, Dash tends to use protected methods for their action and
    // filter callbacks.  but, this method needs to be executed both via the
    // init action and also when the plugin is activated.  therefore, it needs
    // to be public so that the SimpleSymposia object can reference it.
    
    $this->registerSessionPostType();
    $this->registerSymposiumTaxonomy();
  }
  
  private function registerSessionPostType(): void
  {
    $args = [
      'label'               => __('Session', 'simple-symposia'),
      'description'         => __('A session or event', 'simple-symposia'),
      'labels'              => $this->getPostTypeLabels('Session', 'Sessions', 'simple-symposia'),
      'supports'            => ['title', 'editor', 'thumbnail', 'revisions'],
      'rewrite'             => [
        'slug'       => 'session',
        'with_front' => true,
        'pages'      => true,
        'feeds'      => true,
      ],
      'menu_icon'           => 'dashicons-schedule',
      'capability_type'     => 'page',
      'has_archive'         => false,
      'exclude_from_search' => false,
      'hierarchical'        => false,
      'public'              => true,
      'show_ui'             => true,
      'show_in_menu'        => true,
      'show_in_admin_bar'   => true,
      'show_in_nav_menus'   => true,
      'can_export'          => true,
      'publicly_queryable'  => true,
      'show_in_rest'        => true,
      'menu_position'       => 5,
    ];
    
    register_post_type(self::POST_TYPE, $args);
  }
  
  private function registerSymposiumTaxonomy(): void
  {
    $args = [
      'labels'            => $this->getTaxonomyLabels('Symposium', 'Symposia', 'simple-symposia'),
      'hierarchical'      => false,
      'show_tagcloud'     => false,
      'public'            => true,
      'show_ui'           => true,
      'show_admin_column' => true,
      'show_in_nav_menus' => true,
      'show_in_rest'      => true,
      'rewrite'           => [
        'slug'         => 'symposium',
        'with_front'   => true,
        'hierarchical' => false,
      ],
    ];
    
    register_taxonomy(self::TAXONOMY, [self::POST_TYPE], $args);
  }
}
