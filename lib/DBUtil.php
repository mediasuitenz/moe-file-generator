<?php

namespace MOEFileGenerator;
use Aura\Sql\ExtendedPdo;

/**
 * Constructs an Aura\Sql\ExtendedPDO from config.php values
 */
class DBUtil {

  private static $pdo = null;
  
  /**
   * Returns a configured Aura\Sql\ExtendedPdo
   * @return ExtendedPdo
   */
  public static function getConnection() {
    
    //Lazy load config
    if (is_null(self::$pdo)) {

      $config = Config::getConfig();

      assert(!empty($config['dbHost']), 'dbHost not set in config.php');
      assert(!empty($config['dbName']), 'dbName not set in config.php');
      assert(!empty($config['dbUser']), 'dbUser not set in config.php');
      assert(!empty($config['dbPassword']), 'dbPassword not set in config.php');


      self::$pdo = new ExtendedPdo(
          'mysql:host='.$config['dbHost'].';dbname='.$config['dbName'],
          $config['dbUser'],
          $config['dbPassword'],
          array(), // driver options as key-value pairs
          array()  // attributes as key-value pairs
      );
    }

    return self::$pdo;
  }
}
