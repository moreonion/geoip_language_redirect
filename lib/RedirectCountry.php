<?php

namespace Drupal\geoip_language_redirect;

class RedirectCountry extends RedirectBase {
  public function checkAndRedirect() {
    $country = $this->api->getCountry();
    $mapping = $this->api->getMapping();
    if (isset($mapping[$country])) {
      $this->redirect($mapping[$country]);
    } else {
      $this->redirect($this->api->defaultLanguage());
    }
  }
}
