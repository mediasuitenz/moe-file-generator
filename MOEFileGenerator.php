<?php

namespace MOEFileGenerator;
use DateTimeZone, DateTime;

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

    $collectionMonth = $dataArray['meta']['collectionMonth'];
    $collectionYear = $dataArray['meta']['collectionYear'];

    $moeFile = new MOEFile(
      $dataArray['meta']['schoolNumber'],
      $collectionMonth,
      $collectionYear,
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
      $collectionMonth,
      $collectionYear,
      $dataArray['meta']['schoolNumber'],
      self::calculateFTETotal($collectionMonth, $collectionYear, $dataArray['students']),
      $dataArray['meta']['enrolmentScheme'],
      $enrolmentSchemeDate
    ));

    return $moeFile->getPath();
  }

  /**
   * Calculates the total FTE for students in type FF, EX, AE, RA, AD ,RE, TPREOM and TPRAOM
   * who have FIRST ATTENDANCE before march first of collection year and last attendance null
   * or after march first of colleciton year
   *
   * Based on total for table M3, E3, J3 or S3 depending on collection month
   * @param  String $collectionMonth
   * @param  String $collectionYear
   * @param  Array  $studentArray
   * @return String FTE Total
   */
  private static function calculateFTETotal($collectionMonth, $collectionYear, $studentArray) {

    /**
     * Returns true if student type is valid for counting roll
     * and student start and end dates are valid for given collection date
     * @param  DateTime $collectionDate
     * @param  Array    $student
     * @return boolean
     */
    $studentFilter = function($collectionDate, $student) {
      $nzdt = new DateTimeZone('Pacific/Auckland');
      $startDate = new DateTime($student['start_date'], $nzdt);
      $lastAttendance = empty($student['LAST ATTENDANCE']) ? null : new DateTime($student['LAST ATTENDANCE'], $nzdt);
      $validStudentTypes = array('FF', 'EX', 'AE', 'RA', 'AD', 'RE', 'TPREOM', 'TPRAOM');
      return (in_array($student['TYPE'], $validStudentTypes) &&
        $startDate->getTimestamp() <= $collectionDate->getTimestamp() &&
        (is_null($lastAttendance) || $lastAttendance->getTimestamp() >= $collectionDate->getTimestamp));
    };

    // Student TYPE in [FF, EX, AE, RA, AD, RE, TPREOM, TPRAOM]
    $collectionDate;
    $nzdt = new DateTimeZone('Pacific/Auckland');
    switch ($collectionMonth) {
      case 'M':
        // and FIRST ATTENDANCE is <=1 March 2015 or Roll count day
        // and LAST ATTENDANCE is Null or >=1 March 2015 or roll count day
        $collectionDate = new DateTime($collectionYear . '-03-01', $nzdt);
        break;
      case 'E':
        // and FIRST ATTENDANCE is <=31 May 2015
        // and LAST ATTENDANCE is Null or >=31 May 2015
        $collectionDate = new DateTime($collectionYear . '-05-31');
        break;
      case 'J':
        // and FIRST ATTENDANCE is <= 1 July 2015
        // and LAST ATTENDANCE is Null or >=1 July2015
        $collectionDate = new DateTime($collectionYear . '07-01');
        break;
      case 'S':
        // and FIRST ATTENDANCE is <=2 September 2015 or Roll count day
        // and LAST ATTENDANCE is Null or >=2 September 2015 or roll count day
        $collectionDate = new DateTime($collectionYear . '09-02');
        break;
    }

    $total = '0';

    foreach ($studentArray as $student) {
      if ($studentFilter($collectionDate, $student)) {
        $total = bcadd($total, $student['FTE'], 1);
      }
    }

    return $total;
  }
}
