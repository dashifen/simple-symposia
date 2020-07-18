<?php

namespace Dashifen\SimpleSymposia\Agents;

use WP_Term_Query;
use WP_Meta_Query;
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
   * @var SimpleSymposia
   */
  protected $handler;
  
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
      
      $symposium = SimpleSymposia::PREFIX . self::SYMPOSIUM;
      $this->addFilter('manage_edit-' . $symposium . '_columns', 'alterSymposiumColumns');
      $this->addFilter('manage_' . $symposium . '_custom_column', 'fillSymposiumCustomColumns', 10, 3);
      $this->addFilter('manage_edit-' . $symposium . '_sortable_columns', 'addSortableSymposiumColumns');
      $this->addAction('pre_get_terms', 'sortSymposiaByDate');
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
  
  /**
   * alterSymposiumColumns
   *
   * Changes the default list of columns for the symposium term to one that
   * makes more sense for our needs within this plugin.
   *
   * @param array $columns
   *
   * @return array
   */
  protected function alterSymposiumColumns(array $columns): array
  {
    // there's three things we want to do here:  remove the description, change
    // the name of the count column to "Sessions," and add a column for the
    // date(s) of our symposium.  because of the addition, it's easier to make
    // a new set of columns so that we can more accurately control the order
    // of them.
    
    foreach ($columns as $column => $display)
      switch ($column) {
        case 'description':
          
          // to remove the description, we just skip it.  by simply breaking
          // here, we prevent the default action from occurring which leaves
          // the description behind as we add to our set of new columns.
          
          break;
        
        case 'posts':
          $newColumns['posts'] = 'Sessions';
          break;
        
        case 'slug':
          
          // we want the dates to be after the slug, so we first add the slug
          // to our new set of columns, then we add the dates.
          
          $newColumns['slug'] = $display;
          $newColumns['dates'] = 'Date(s)';
          break;
        
        default:
          $newColumns[$column] = $display;
          break;
      }
    
    return $newColumns ?? [];
  }
  
  /**
   * fillSymposiumCustomColumns
   *
   * Gets the information we need for this term's custom column.
   *
   * @param string $display
   * @param string $column
   * @param int    $termId
   *
   * @return string
   */
  protected function fillSymposiumCustomColumns(string $display, string $column, int $termId)
  {
    if ($column === 'dates') {
      $dateFormat = get_option('date_format');
      $source = SimpleSymposia::PREFIX . self::SYMPOSIUM . '_' . $termId;
      $s = get_field('simpsymp-symposia-starting-date', $source);
      $e = get_field('simpsymp-symposia-ending-date', $source);
      
      // the starting date ($s) is required, but the ending one ($e) is optional.
      // so, our display is contingent on the existence of an ending date.  also,
      // conveniently, these dates are returned to us as UNIX timestamps.
      
      $display = date($dateFormat, $s);
      
      if (!empty($e)) {
        $display .= ' &ndash; ' . date($dateFormat, $e);
      }
    }
    
    return $display;
  }
  
  /**
   * addSortableSymposiumColumns
   *
   * Adds the dates column to the list of sortable columns for our symposium
   * taxonomy.
   *
   * @param array $columns
   *
   * @return array
   */
  protected function addSortableSymposiumColumns(array $columns): array
  {
    $columns['dates'] = 'dates';
    return $columns;
  }
  
  /**
   * sortSymposiaByDate
   *
   * Identifies when symposia should be ordered by dates and then does so as
   * needed.
   *
   * @param WP_Term_Query $termQuery
   *
   * @return WP_Term_Query
   */
  protected function sortSymposiaByDate(WP_Term_Query $termQuery): WP_Term_Query
  {
    if ($this->handler->isSymposiumEditor()) {
      if (($_GET['orderby'] ?? '') === 'dates') {
        
        // now that we've confirmed that we're on the symposium term edit page
        // and that the dates field has been selected as the one by which we
        // are to sort, we actually need to tell WP how to do that.  the
        // WP_Term_Query doesn't have get/set methods, so we have to mess with
        // the query vars directly.  for our orderby value, we use a named
        // query; see https://bit.ly/2Cg8Jvm for more information.
        
        $termQuery->query_vars['orderby'] = 'dateClause';
        $termQuery->query_vars['order'] = $_GET['order'] ?? 'DESC';
        $termQuery->meta_query = new WP_Meta_Query(
          [
            'dateClause' => [
              
              // ACF stores date fields in Ymd format.  this makes it easy to
              // order by them as numbers.
              
              'key'  => 'simpsymp-symposia-starting-date',
              'type' => 'NUMERIC',
            ],
          ]
        );
      }
    }
    
    return $termQuery;
  }
}
