<?php

class CountrySelectorRedirectTest extends DrupalUnitTestCase {
  protected static $mapping = array(
    'AT' => 'de',
    'GB' => 'en',
    'FR' => 'fr',
  );
  protected static $links = array(
    'de' => 'node/47',
    'en' => 'node/11',
  );

  protected static function instance($country, $default, $path) {
    return new CountrySelectorRedirect($path, self::$links, self::$mapping, $country, $default);
  }

  function testSelectPath_ifNoTranslationsAndDefault() {
    $path = 'node/49';
    $redirector = self::instance('FR', 'fr', $path);
    $this->assertEqual($path, $redirector->selectPath());
  }

  function testSelectPath_defaultLanguage() {
    $redirector = self::instance('FR', 'en', 'node/49');
    $this->assertEqual(self::$links['en'], $redirector->selectPath());
  }

  function testSelectPath_unknownCountry() {
    $redirector = self::instance('CH', 'en', 'node/49');
    $this->assertEqual(self::$links['en'], $redirector->selectPath());
  }

  function testSelectPath_mapped() {
    $redirector = self::instance('AT', 'en', 'node/49');
    $this->assertEqual(self::$links['de'], $redirector->selectPath());
  }
}
