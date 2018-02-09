<?php

namespace Drupal\geoip_language_redirect;

/**
 * Wrap Drupal methods to make this module
 * unit-testable.
 */
class Drupal {
  protected $originalCache = NULL;
  protected $header = NULL;

  public function currentPath() {
    return $_GET['q'];
  }

  public function currentParameters() {
    return drupal_get_query_parameters();
  }

  public function defaultLanguage() {
    return \variable_get(
      'geoip_language_redirect_default_language',
      \language_default()->language
    );
  }

  public function url($path, $language, $options = array()) {
    $options = array('language' => $language) + $options;
    if (!isset($options['query'])) {
      $options['query'] = $this->currentParameters();
    }
    return url($path, $options);
  }
  public function switchLinks($path) {
    require_once DRUPAL_ROOT . '/includes/language.inc';

    $links = \language_negotiation_get_switch_links('language', $path);
    if (!$links) {
      return NULL;
    }
    $current_language = $GLOBALS['language']->language;
    unset($links->links[$current_language]);
    if (variable_get('site_frontpage', 'node') == $path) {
      $front_links = \language_negotiation_get_switch_links('language', '');
      foreach ($links->links as $lang => $link) {
        if (empty($link['href'])) {
          $links->links[$lang] = $front_links->links[$lang];
        }
      }
    }
    // Remove remaining non-links.
    foreach ($links->links as $lang => $link) {
      if (!isset($link['href'])) {
        unset($links->links[$lang]);
      }
    }
    return $links ? $links->links : NULL;
  }
  /**
   * Check if the current logged-in user has access to a path.
   */
  public function checkAccess($path, $langCode) {
    // Extra handling for front-page.
    if (empty($path)) {
      if (module_exists('i18n_variable')) {
      	$path = \i18n_variable_get('site_frontpage', $langCode, $path);
      } else {
      	$path = \variable_get('site_frontpage', $path);
      }
    }
    return ($router_item = \menu_get_item($path)) && $router_item['access'];
  }

  /**
   * Get list of accessible translation links.
   */
  public function translationLinks($path) {
    $accessible_links = [];
    if ($links = $this->switchLinks($path)) {
      foreach ($links as $lang => $link) {
        if ($this->checkAccess($link['href'], $lang)) {
          $url = $this->url($link['href'], $link['language']);
          $accessible_links[$lang] = $url ? $url : '/';
        }
      }
    }
    return $accessible_links;
  }

  /**
   * Get current users country from GeoIP.
   *
   * If geoip debugging is enabled (=the variable geoip_debug has a non-empty
   * value) the outcome of the country detection can be overridden using $_GET
   * variables:
   * - $_GET['geoip_country'] overrides the detected country.
   * - $_GET['geoip'] simulates a request from a specific IP address.
   *
   * @return null|string
   *   A two character country code or NULL, if geoip detection failed.
   */
  public function getCountry() {
    $ip_address = ip_address();
    if (variable_get('geoip_debug', FALSE)) {
      if (isset($_GET['geoip_country'])) {
        return $_GET['geoip_country'];
      }
      if (isset($_GET['geoip'])) {
        $ip_address = $_GET['geoip'];
      }
    }

    // use @: see https://bugs.php.net/bug.php?id=59753
    if (function_exists('geoip_country_code_by_name')) {
      return @geoip_country_code_by_name($ip_address);
    } else {
      watchdog('geoip_language_redirect', 'geoip_country_code_by_name() does not exist. Check your installation.', array(), WATCHDOG_WARNING);
      return NULL;
    }
  }
  
  /**
   * Get mapping from ISO country-codes to language-codes.
   */
  public function getMapping() {
    return variable_get('geoip_redirect_mapping', array());
  }
  
  public function baseUrl() {
    return $GLOBALS['base_url'];
  }
  
  public function referer() {
    return $_SERVER['HTTP_REFERER'];
  }

  public function userAgent() {
    return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : FALSE;
  }
}
