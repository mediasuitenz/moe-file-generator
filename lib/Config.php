<?php

namespace MOEFileGenerator;

class Config {

  private static $config = null;

  public static function getConfig() {
    if (is_null(self::$config)) {
      //Load config file
      if (getenv('ENVIRONMENT') !== 'TEST') {
        self::$config = require dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config.php';
      } else {
        self::$config = require dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config-test.php';
      }

      assert(!is_null(self::$config) && is_array(self::$config), 'config.php must return a valid array of config options');
    }
    return self::$config;
  }

}
