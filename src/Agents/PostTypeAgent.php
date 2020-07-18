<?php

namespace Dashifen\SimpleSymposia\Agents;

use Dashifen\SimpleSymposia\SimpleSymposia;
use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Agents\AbstractPluginAgent;
use Dashifen\WPHandler\Traits\PostTypeRegistrationTrait;
use Dashifen\WPHandler\Traits\TaxonomyRegistrationTrait;

class PostTypeAgent extends AbstractPluginAgent
{
  use PostTypeRegistrationTrait;
  use TaxonomyRegistrationTrait;
  
  public const SESSION = 'session';
  public const SPEAKER = 'speaker';
  public const SYMPOSIUM = 'symposium';
  public const TOPIC = 'topic';
  
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
    if (!$this->isInitialized()) {
      $this->addAction('init', 'register');
    }
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
    $this->registerSpeakerPostType();
    $this->registerSymposiumTaxonomy();
    $this->registerTopicTaxonomy();
  }
  
  /**
   * registerSessionPostType
   *
   * Registers the session post type.
   *
   * @return void
   */
  private function registerSessionPostType(): void
  {
    $args = [
      'label'               => __('Session', 'simple-symposia'),
      'description'         => __('A session or event', 'simple-symposia'),
      'labels'              => $this->getPostTypeLabels('Session', 'Sessions', 'simple-symposia'),
      'supports'            => ['title', 'editor', 'thumbnail', 'custom-fields', 'revisions'],
      'rewrite'             => [
        'slug'       => self::SESSION,
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
      'menu_position'       => 6,
    ];
    
    register_post_type(SimpleSymposia::PREFIX . self::SESSION, $args);
  }
  
  /**
   * registerSpeakerPostType
   *
   * Registers the speaker post type.
   *
   * @return void
   */
  private function registerSpeakerPostType(): void
  {
    $args = [
      'label'               => __('Speaker', 'simple-symposia'),
      'description'         => __("A session's speaker", 'simple-symposia'),
      'labels'              => $this->getPostTypeLabels('Speaker', 'Speakers', 'simple-symposia'),
      'supports'            => ['title', 'editor', 'thumbnail', 'custom-fields', 'revisions'],
      'rewrite'             => [
        'slug'       => self::SPEAKER,
        'with_front' => true,
        'pages'      => true,
        'feeds'      => true,
      ],
      'menu_icon'           => 'dashicons-groups',
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
      'menu_position'       => 7,
    ];
    
    register_post_type(SimpleSymposia::PREFIX . self::SPEAKER, $args);
  }
  
  /**
   * registerSymposiumTaxonomy
   *
   * Registers the symposium taxonomy and links it to the session post type.
   *
   * @return void
   */
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
        'slug'         => self::SYMPOSIUM,
        'hierarchical' => false,
        'with_front'   => true,
      ],
    ];
    
    register_taxonomy(
      SimpleSymposia::PREFIX . self::SYMPOSIUM,
      [SimpleSymposia::PREFIX . self::SESSION],
      $args
    );
  }
  
  /**
   * registerTopicTaxonomy
   *
   * Registers a topic taxonomy and links it to the session post type.
   *
   * @return void
   */
  private function registerTopicTaxonomy(): void
  {
    $args = [
      'labels'            => $this->getTaxonomyLabels('Topic', 'Topics', 'simple-symposia'),
      'hierarchical'      => false,
      'show_tagcloud'     => false,
      'public'            => true,
      'show_ui'           => true,
      'show_admin_column' => true,
      'show_in_nav_menus' => true,
      'show_in_rest'      => true,
      'rewrite'           => [
        
        // in case we ever add some other form of topic other than the ones
        // related to symposia sessions, we're going to link these topics to
        // symposia in their URL.  we'll create a symposium/topics page that
        // will act as the "archive" of these terms elsewhere.
        
        'slug'         => self::SYMPOSIUM . '/' . self::TOPIC,
        'hierarchical' => false,
        'with_front'   => true,
      ],
    ];
    
    register_taxonomy(
      SimpleSymposia::PREFIX . self::TOPIC,
      [SimpleSymposia::PREFIX . self::SESSION],
      $args
    );
  }
}
