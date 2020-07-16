<?php
/**
 * Plugin Name: Simple Symposia
 * Description: A WordPress plugin to create sessions and group them into symposia, conferences, or other similar events.
 * Author URI: mailto:dashifen@dashifen.com
 * Author: David Dashifen Kees
 * Text Domain: simple-symposia
 * Version: 0.0.0
 *
 * @noinspection PhpStatementHasEmptyBodyInspection
 * @noinspection PhpIncludeInspection
 */

use Dashifen\SimpleSymposia\SimpleSymposia;
use Dashifen\WPHandler\Handlers\HandlerException;

// the if-condition defines the location that Dash tends to use for their
// Composer autoloader.  the next three conditions should help to identify
// other common locations for it.  if, in the end, it doesn't find a more
// global option, it'll assume that there's a vendor folder adjacent to this
// file.  and, in the end, if it can't find one at all, then the require_once
// call will cause a PHP error.

if (file_exists($autoloader = dirname(ABSPATH) . '/deps/vendor/autoload.php'));
elseif ($autoloader = file_exists(dirname(ABSPATH) . '/vendor/autoload.php'));
elseif ($autoloader = file_exists(ABSPATH . 'vendor/autoload.php'));
else $autoloader = 'vendor/autoload.php';
require_once $autoloader;

(function () {
  
  // by instantiating objects in this anonymous function, we don't add the
  // variables listed herein to the WordPress global scope.  it's unlikely that
  // we'd cause a collision with another variable, but this guarantees that we
  // don't.
  
  try {
    $simpleSymposia = new SimpleSymposia();
    $simpleSymposia->initialize();
  } catch (HandlerException $e) {
    wp_die($e->getMessage());
  }
})();
