<?php

namespace MOEFileGenerator;

//Load libraries installed by composer
require 'vendor/autoload.php';

//Load config file
if (getenv('ENVIRONMENT') !== 'TEST') {
  $config = require dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php';
} else {
  $config = require dirname(__FILE__).DIRECTORY_SEPARATOR.'config-test.php';
}

assert(!is_null($config) && is_array($config), 'config.php must return a valid array of config options');

/**
 * Generates a .moe file from the provided student data
 * and stores it in the directory provided by config.php
 */
class MOEFileGenerator {

  /**
   * Returns an array of config variables
   * @return Array
   */
  public static function getConfig() {
    global $config;
    return $config;
  }

  /**
   * Generates a .moe file from an array of student and
   * meta data. Stores file in directory provided by config
   *
   * Data array must be of the following format:
   * 
   * array(
   *   'meta' => array(
   *     'smsName' => '',
   *     'smsVersion' => '',
   *     'collectionMonth' => 'M',
   *     'collectionYear' => '2015',
   *     'enrolmentScheme' => '',
   *     'enrolmentSchemeDate' => '',
   *     'authorisingUser' => '',
   *     'schoolNumber' => '',
   *     'isDraft' => true'
   *   ),
   *   'students' => array(
   *     ...one array for each student e.g.
   *     array(
   *       'first_name' => '',
   *       'last_name' => '',
   *       ...etc...
   *     )
   *   )
   * )
   * 
   * @param  Array $dataArray  Array consisting of meta and student arrays
   * @return String            Path to .moe file
   * @throws Exception         IO Exception if file could not be written
   */
  public static function generateMOE($dataArray) {

    $moeFile = new MOEFile(
      $dataArray['meta']['schoolNumber'],
      $dataArray['meta']['collectionMonth'],
      $dataArray['meta']['collectionYear'],
      $dataArray['meta']['isDraft'],
      //TODO: Get version
      '1',
      self::getConfig()['moeFileDirectory']
    );

    $enrolmentSchemeDate = '00000000';
    if ($dataArray['meta']['enrolmentScheme'] === 'Y') {
      $enrolmentSchemeDate = $dataArray['meta']['enrolmentSchemeDate'];
    }

    //Write the header
    $moeFile->writeLine(array(
      $dataArray['meta']['smsName'],
      $dataArray['meta']['smsVersion'],
      $dataArray['meta']['collectionMonth'],
      $dataArray['meta']['collectionYear'],
      $dataArray['meta']['schoolNumber'],
      //TODO: Calculate FTE total
      '1010',
      $dataArray['meta']['enrolmentScheme'],
      $enrolmentSchemeDate
    ));

    return $moeFile->getPath();
  }

}
