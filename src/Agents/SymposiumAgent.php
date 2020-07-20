<?php

namespace Dashifen\SimpleSymposia\Agents;

use WP_Meta_Query;
use WP_Term_Query;
use Dashifen\SimpleSymposia\SimpleSymposia;
use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Agents\AbstractPluginAgent;

class SymposiumAgent extends AbstractPluginAgent
{
  /**
   * @var SimpleSymposia
   */
  protected $handler;
  
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
      $symposium = SimpleSymposia::PREFIX . PostTypeAgent::SYMPOSIUM;
      $this->addFilter('manage_edit-' . $symposium . '_columns', 'alterSymposiumColumns');
      $this->addFilter('manage_' . $symposium . '_custom_column', 'fillSymposiumCustomColumns', 10, 3);
      $this->addFilter('manage_edit-' . $symposium . '_sortable_columns', 'addSortableSymposiumColumns');
      $this->addAction('pre_get_terms', 'sortSymposiaByDate');
    }
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
  
  private function getCurrentColumnContents(int $termId): string
  {
    $isCurrent = get_term_meta($termId, 'simpsymp-symposia-is-current', false);
    
  }
  
  private function getDatesColumnContent(int $termId): string
  {
    $dateFormat = get_option('date_format');
    $source = SimpleSymposia::PREFIX . PostTypeAgent::SYMPOSIUM . '_' . $termId;
    $s = get_field('simpsymp-symposia-starting-date', $source);
    $e = get_field('simpsymp-symposia-ending-date', $source);
    
    // the starting date ($s) is required, but the ending one ($e) is optional.
    // so, our display is contingent on the existence of an ending date.  also,
    // conveniently, these dates are returned to us as UNIX timestamps.
    
    $display = date($dateFormat, $s);
    
    if (!empty($e)) {
      $display .= ' &ndash; ' . date($dateFormat, $e);
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
  
  
  /**
   * isSymposiumEditor
   *
   * Returns true if we're on the edit-tags.php page and we're specifically
   * editing our symposium taxonomy.  This method is publi and static so the
   * SimpleSymposia object (at least) can access it.
   *
   * @return bool
   */
  public static function isSymposiumEditor(): bool
  {
    $screen = get_current_screen();
    $symposium = SimpleSymposia::PREFIX . PostTypeAgent::SYMPOSIUM;
    return $screen->base === 'edit-tags' && $screen->taxonomy === $symposium;
  }
}
