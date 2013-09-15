<?php

namespace Drupal\geoip_language_redirect;

/**
 * Wrap Drupal methods to maket this module
 * unit-testable.
 */
class Drupal {
  protected $originalCache = NULL;

  public function readCookie() {
    \session_cache_get('geoip_redirect');
  }
  /**
   * Set the language cookie.
   */
  public function setCookie($value) {
    \session_cache_set('geoip_redirect', $value);
  }
  public function currentPath() {
    return $_GET['q'];
  }
  public function currentLanguage() {
    return $GLOBALS['language']->language;
  }
  public function defaultLanguage() {
    return \language_default()->language;
  }
  public function redirect($path, $language) {
    \drupal_goto($path, array('language' => $language));
  }
  public function switchLinks($path) {
    $links = \language_negotiation_get_switch_links('language', $path);
    return $links ? $links->links : NULL;
  }
  /**
   * Check if the current logged-in user has access to a path.
   */
  public function checkAccess($path) {
    return ($router_item = \menu_get_item($path)) && $router_item['access'];
  }

  /**
   * Disable Drupal's page-cache during hook_boot().
   */
  public function disableCache() {
    if ($this->originalCache = \variable_get('cache')) {
      $GLOBALS['conf']['cache'] = FALSE;
    }
  }
  
  /**
   * Serve the page from cache and end execution.
   */
  public function serveFromCache() {
    if ($this->originalCache) {
      $GLOBALS['conf']['cache'] = $this->originalCache;
      $cache = \drupal_page_get_cache();
      if (\is_object($cache)) {
        \header('X-Drupal-Cache: HIT');
        \drupal_serve_page_from_cache($cache);
        if (\variable_get('page_cache_invoke_hooks', TRUE)) {
          \bootstrap_invoke_all('exit');
        }
        // We are done.
        exit;
      }
    }
  }
  
  /**
   * Get current users country from GeoIP.
   */
  public function getCountry() {
    // use @: see https://bugs.php.net/bug.php?id=59753
    if (function_exists('geoip_country_code_by_name')) {
      return @geoip_country_code_by_name(ip_address());
    }
  }
  
  /**
   * Get mapping from ISO country-codes to language-codes.
   */
  public function getMapping() {
    variable_get('geoip_redirect_mapping', array());
  }
  
  public function baseUrl() {
    return $GLOBALS['base_url'];
  }
  
  public function referer() {
    return $_SERVER['HTTP_REFERER'];
  }
}
