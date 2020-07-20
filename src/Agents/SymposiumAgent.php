<?php

namespace Dashifen\SimpleSymposia\Agents;

use WP_Term;
use WP_Meta_Query;
use WP_Term_Query;
use Dashifen\SimpleSymposia\SimpleSymposia;
use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Agents\AbstractPluginAgent;
use Dashifen\WPHandler\Traits\OptionsManagementTrait;

class SymposiumAgent extends AbstractPluginAgent
{
  use OptionsManagementTrait;
  
  /**
   * @var SimpleSymposia
   */
  protected $handler;
  
  /**
   * getOptionNames
   *
   * Returns an array of the options that this agent manages.
   *
   * @return array
   */
  protected function getOptionNames(): array
  {
    return ['simpsymp-current-symposium'];
  }
  
  /**
   * initialize
   *
   * Hooks this object's protected methods into the WordPress ecosystem.
   *
   * @return void
   * @throws HandlerException
   */
  public function initialize(): void
  {
    if (!$this->isInitialized()) {
      $this->addAction('admin_enqueue_scripts', 'addAssets');
      
      // we've one ajax request to handle here:  the setting of the current
      // symposium.
      
      $this->addAction('wp_ajax_simpsymp-set-current-symposium', 'ajaxSetCurrentSymposium');
      
      // then, these all alter and control various aspects of the symposium
      // edit-tags.php page.  primarily, we add/remove some columns from the
      // table and tell WP how to sort by the symposium dates.
      
      $symposium = $this->getSymposiumTaxonomyName();
      $this->addFilter('manage_edit-' . $symposium . '_columns', 'alterSymposiumColumns');
      $this->addFilter('manage_' . $symposium . '_custom_column', 'fillSymposiumCustomColumns', 10, 3);
      $this->addFilter('manage_edit-' . $symposium . '_sortable_columns', 'addSortableSymposiumColumns');
      $this->addAction('pre_get_terms', 'sortSymposiaByDate');
    }
  }
  
  /**
   * getSymposiumTaxonomyName
   *
   * Returns the full name of the symposium taxonomy.
   *
   * @return string
   */
  private function getSymposiumTaxonomyName(): string
  {
    return SimpleSymposia::PREFIX . PostTypeAgent::SYMPOSIUM;
  }
  
  /**
   * addAssets
   *
   * Adds CSS and JS assets necessary for the symposium editor to work.
   *
   * @return void
   */
  protected function addAssets(): void
  {
    if ($this->isSymposiumEditor()) {
      $this->enqueue('assets/css/symposium.css');
      $this->enqueue('assets/js/min/symposium.js');
    }
  }
  
  /**
   * isSymposiumEditor
   *
   * Returns true if we're on the edit-tags.php page and we're specifically
   * editing our symposium taxonomy.
   *
   * @return bool
   */
  private function isSymposiumEditor(): bool
  {
    $screen = get_current_screen();
    return $screen->taxonomy === $this->getSymposiumTaxonomyName()
      && $screen->base === 'edit-tags';
  }
  
  /**
   * ajaxSetCurrentSymposium
   *
   * Getting a symposium term ID out of the $_POST data, sets it as the current
   * symposium.
   *
   * @return void
   * @throws HandlerException
   */
  protected function ajaxSetCurrentSymposium(): void
  {
    $termId = $_POST['termId'] ?? -1;
    
    
    // as long as get_term response with a WP_Term object, we're good to go.
    // if we get a WP_Error, then the following if-condition would fail and
    // we send that failure back to the client.
    
    if ($success = $this->isSymposium($termId)) {
      $this->updateOption('simpsymp-current-symposium', $termId);
    }
    
    header('Content-type: application/json');
    echo json_encode(['success' => $success]);
    wp_die();
  }
  
  /**
   * isSymposium
   *
   * Returns true if the parameter is an ID of a term in the symposium
   * taxonomy.  This method is public so that it can be accessed by other
   * objects, e.g. the theme which presents this information to visitors.
   *
   * @param int $termId
   *
   * @return bool
   */
  public function isSymposium(int $termId): bool
  {
    return get_term($termId, $this->getSymposiumTaxonomyName()) instanceof WP_Term;
  }
  
  /**
   * alterSymposiumColumns
   *
   * Changes the default list of columns for symposium terms.
   *
   * @param array $columns
   *
   * @return array
   */
  protected function alterSymposiumColumns(array $columns): array
  {
    // there's three things we want to do here:  remove the description, change
    // the name of the count column to "Sessions," and add custom column for
    // the date(s) of our symposium and the current one.  because of the
    // additions, it's easier to make a new set of columns so that we can more
    // accurately control the order of them.
    
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
          
          // we want the current column before the slug and the dates to be
          // after it, so we first add the slug to our new set of columns,
          // then we add the dates.
          
          $newColumns['current'] = 'Current';
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
   * @throws HandlerException
   */
  protected function fillSymposiumCustomColumns(string $display, string $column, int $termId)
  {
    if ($column !== 'current' && $column !== 'dates') {
      return $display;
    }
    
    return $column === 'current'
      ? $this->getCurrentColumnContents($termId)
      : $this->getDatesColumnContent($termId);
  }
  
  /**
   * getCurrentColumnContents
   *
   * Creates an HTML checkbox input to add to the screen for the current
   * column.  The symposium that is marked as the current in our options has a
   * marked box; the rest will be unmarked.
   *
   * @param int $termId
   *
   * @return string
   * @throws HandlerException
   */
  private function getCurrentColumnContents(int $termId): string
  {
    $term = get_term($termId, $this->getSymposiumTaxonomyName());
    $isCurrent = $this->isCurrentSymposium($termId);
    
    /** @noinspection HtmlUnknownAttribute */
    
    $display = <<< DISPLAY
      <label>
        <span class="screen-reader-text">
          If this checkbox is marked, then the %s symposium is the current one.
        </span>
        <input type="checkbox" name="current-symposium" value="%d" %s>
      </label>
DISPLAY;
    
    return sprintf($display, $term->name, $termId, ($isCurrent ? 'checked' : ''));
  }
  
  /**
   * isCurrentSymposium
   *
   * Returns true if the symposium identified by the parameter is the one that
   * is marked current in the options table.  This method is public because we
   * suspect that it'll be useful to the theme which displays this information.
   *
   * @param int $termId
   *
   * @return bool
   * @throws HandlerException
   */
  public function isCurrentSymposium(int $termId): bool
  {
    return $this->getOption('simpsymp-current-symposium', -1) === $termId;
  }
  
  /**
   * getDatesColumnContent
   *
   * Constructs contents for date cells for symposia.
   *
   * @param int $termId
   *
   * @return string
   */
  private function getDatesColumnContent(int $termId): string
  {
    return $this->getSymposiumDateDisplay($termId);
  }
  
  /**
   * getSymposiumDateDisplay
   *
   * Returns a date display for the given symposium.  This method is public
   * because we suspect it'll be useful to the theme which presents this
   * information to visitors.
   *
   * @param int $termId
   *
   * @return string
   */
  public function getSymposiumDateDisplay(int $termId): string
  {
    if ($this->isSymposium($termId)) {
      
      // as long as our term ID is one for the symposium taxonomy, we know we
      // can get the following data out of the database.  the starting date
      // ($s) is required, but the ending one ($e) is optional.  so, our
      // display is contingent on the existence of an ending date.  also,
      // conveniently, these dates are returned to us as UNIX timestamps.
      
      $dateFormat = get_option('date_format');
      $source = $this->getSymposiumTaxonomyName() . '_' . $termId;
      $s = get_field('simpsymp-symposia-starting-date', $source);
      $e = get_field('simpsymp-symposia-ending-date', $source);

      $display = date($dateFormat, $s);
  
      if (!empty($e)) {
        $display .= ' &ndash; ' . date($dateFormat, $e);
      }
    }
    
    return $display ?? '';
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
    if (self::isSymposiumEditor()) {
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
