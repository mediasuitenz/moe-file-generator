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
   *     'approver' => '',
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

    $m3Data = self::calculateSchoolRollByType($collectionMonth, $collectionYear, $dataArray['students']);
    $m4Data = self::calculateHighestLevelMaori($collectionMonth, $collectionYear, $dataArray['students']);

    //Write the header
    $moeFile->writeLine(array(
      $dataArray['meta']['smsName'],
      $dataArray['meta']['smsVersion'],
      $collectionMonth,
      $collectionYear,
      $dataArray['meta']['schoolNumber'],
      $m3Data['total'],
      $dataArray['meta']['enrolmentScheme'],
      $enrolmentSchemeDate
    ));

    //Write student data
    foreach ($dataArray['students'] as $student) {
      //Write the student row
      $moeFile->writeLine(array(
        //1 SCHOOL_ID
        $dataArray['meta']['schoolNumber'],
        //2 STUDENT_ID
        $student['person_id'],
        //3 NSN
        $student['nsn'],
        //4 SURNAME
        $student['last_name'],
        //5 FIRSTNAME
        $student['first_name'],
        //6 GENDER
        $student['gender'],
        //7 DOB
        $student['dob'],
        //8 FIRST ATTENDANCE
        $student['start_date'],
        //9 FIRST SCHOOLING
        $student['first_schooling'],
        //10 ETHNIC1
        $student['ethnic_origin'],
        //11 ETHNIC2
        $student['ethnic_origin2'],
        //12 ETHNIC3
        $student['ethnic_origin3'],
        //13 IWI
        $student['IWI1'],
        //14 IWI2
        $student['IWI2'],
        //15 IWI3
        $student['IWI3'],
        //16 ORS and Section 9
        $student['ORS and Section 9'],
        //17 FUNDING YEAR LEVEL
        $student['funding_year_level'],
        //18 TYPE
        $student['TYPE'],
        //19 PREVIOUS SCHOOL
        $student['previous_school'],
        //20 ZONING STATUS
        $student['zoning'],
        //21 COUNTRY OF CITIZENSHIP
        $student['citizenship'],
        //22 FEE
        $student['FEE'],
        //23 FTE
        $student['FTE'],
        //24 MAORI
        $student['MAORI'],
        //25 LAST ATTENDANCE
        $student['LAST ATTENDANCE'],
        //26 NQF QUAL
        $student['NQF QUAL'],
        //27 REASON
        $student['REASON'],
        //28 ECE
        $student['ECE'],
        //29 PACIFIC MEDIUM - LANGUAGE
        $student['PACIFIC MEDIUM -LANGUAGE'],
        //30 PACIFIC MEDIUM - LEVEL
        $student['PACIFIC MEDIUM - LEVEL'],
        //31 SUBJECT 1
        $student['SUBJECT 1'],
        //32 MODE OF INSTRUCTION SUBJECT 1
        $student['MODE OF INSTRUCTION SUBJECT 1'],
        //33 HOURS PER YEAR SUBJECT 1
        $student['HOURS PER YEAR SUBJECT 1'],
        //34 INSTRUCTIONAL YEAR LEVEL SUBJECT 1
        $student['INSTRUCTIONAL YEAR LEVEL SUBJECT 1'],
        //35 SUBJECT 2
        $student['SUBJECT 2'],
        //36 MODE OF INSTRUCTION SUBJECT 2
        $student['MODE OF INSTRUCTION SUBJECT 2'],
        //37 HOURS PER YEAR SUBJECT 2
        $student['HOURS PER YEAR SUBJECT 2'],
        //38 INSTRUCTIONAL YEAR LEVEL SUBJECT 2
        $student['INSTRUCTIONAL YEAR LEVEL SUBJECT 2'],
        //39 SUBJECT 3
        $student['SUBJECT 3'],
        //40 MODE OF INSTRUCTION SUBJECT 3
        $student['MODE OF INSTRUCTION SUBJECT 3'],
        //41 HOURS PER YEAR SUBJECT 3
        $student['HOURS PER YEAR SUBJECT 3'],
        //42 INSTRUCTIONAL YEAR LEVEL SUBJECT 3
        $student['INSTRUCTIONAL YEAR LEVEL SUBJECT 3'],
        //43 SUBJECT 4
        $student['SUBJECT 4'],
        //44 MODE OF INSTRUCTION SUBJECT 4
        $student['MODE OF INSTRUCTION SUBJECT 4'],
        //45 HOURS PER YEAR SUBJECT 4
        $student['HOURS PER YEAR SUBJECT 4'],
        //46 INSTRUCTIONAL YEAR LEVEL SUBJECT 4
        $student['INSTRUCTIONAL YEAR LEVEL SUBJECT 4'],
        //47 SUBJECT 5
        $student['SUBJECT 5'],
        //48 MODE OF INSTRUCTION SUBJECT 5
        $student['MODE OF INSTRUCTION SUBJECT 5'],
        //49 HOURS PER YEAR SUBJECT 5
        $student['HOURS PER YEAR SUBJECT 5'],
        //50 INSTRUCTIONAL YEAR LEVEL SUBJECT 5
        $student['INSTRUCTIONAL YEAR LEVEL SUBJECT 5'],
        //51 SUBJECT 6
        $student['SUBJECT 6'],
        //52 MODE OF INSTRUCTION SUBJECT 6
        $student['MODE OF INSTRUCTION SUBJECT 6'],
        //53 HOURS PER YEAR SUBJECT 6
        $student['HOURS PER YEAR SUBJECT 6'],
        //54 INSTRUCTIONAL YEAR LEVEL SUBJECT 6
        $student['INSTRUCTIONAL YEAR LEVEL SUBJECT 6'],
        //55 SUBJECT 7
        $student['SUBJECT 7'],
        //56 MODE OF INSTRUCTION SUBJECT 7
        $student['MODE OF INSTRUCTION SUBJECT 7'],
        //57 HOURS PER YEAR SUBJECT 7
        $student['HOURS PER YEAR SUBJECT 7'],
        //58 INSTRUCTIONAL YEAR LEVEL SUBJECT 7
        $student['INSTRUCTIONAL YEAR LEVEL SUBJECT 7'],
        //59 SUBJECT 8
        $student['SUBJECT 8'],
        //60 MODE OF INSTRUCTION SUBJECT 8
        $student['MODE OF INSTRUCTION SUBJECT 8'],
        //61 HOURS PER YEAR SUBJECT 8
        $student['HOURS PER YEAR SUBJECT 8'],
        //62 INSTRUCTIONAL YEAR LEVEL SUBJECT 8
        $student['INSTRUCTIONAL YEAR LEVEL SUBJECT 8'],
        //63 SUBJECT 9
        $student['SUBJECT 9'],
        //64 MODE OF INSTRUCTION SUBJECT 9
        $student['MODE OF INSTRUCTION SUBJECT 9'],
        //65 HOURS PER YEAR SUBJECT 9
        $student['HOURS PER YEAR SUBJECT 9'],
        //66 INSTRUCTIONAL YEAR LEVEL SUBJECT 9
        $student['INSTRUCTIONAL YEAR LEVEL SUBJECT 9'],
        //67 SUBJECT 10
        $student['SUBJECT 10'],
        //68 MODE OF INSTRUCTION SUBJECT 10
        $student['MODE OF INSTRUCTION SUBJECT 10'],
        //69 HOURS PER YEAR SUBJECT 10
        $student['HOURS PER YEAR SUBJECT 10'],
        //70 INSTRUCTIONAL YEAR LEVEL SUBJECT 10
        $student['INSTRUCTIONAL YEAR LEVEL SUBJECT 10'],
        //71 SUBJECT 11
        $student['SUBJECT 11'],
        //72 MODE OF INSTRUCTION SUBJECT 11
        $student['MODE OF INSTRUCTION SUBJECT 11'],
        //73 HOURS PER YEAR SUBJECT 11
        $student['HOURS PER YEAR SUBJECT 11'],
        //74 INSTRUCTIONAL YEAR LEVEL SUBJECT 11
        $student['INSTRUCTIONAL YEAR LEVEL SUBJECT 11'],
        //75 SUBJECT 12
        $student['SUBJECT 12'],
        //76 MODE OF INSTRUCTION SUBJECT 12
        $student['MODE OF INSTRUCTION SUBJECT 12'],
        //77 HOURS PER YEAR SUBJECT 12
        $student['HOURS PER YEAR SUBJECT 12'],
        //78 INSTRUCTIONAL YEAR LEVEL SUBJECT 12
        $student['INSTRUCTIONAL YEAR LEVEL SUBJECT 12'],
        //79 SUBJECT 13
        $student['SUBJECT 13'],
        //80 MODE OF INSTRUCTION SUBJECT 13
        $student['MODE OF INSTRUCTION SUBJECT 13'],
        //81 HOURS PER YEAR SUBJECT 13
        $student['HOURS PER YEAR SUBJECT 13'],
        //82 INSTRUCTIONAL YEAR LEVEL SUBJECT 13
        $student['INSTRUCTIONAL YEAR LEVEL SUBJECT 13'],
        //83 SUBJECT 14
        $student['SUBJECT 14'],
        //84 MODE OF INSTRUCTION SUBJECT 14
        $student['MODE OF INSTRUCTION SUBJECT 14'],
        //85 HOURS PER YEAR SUBJECT 14
        $student['HOURS PER YEAR SUBJECT 14'],
        //86 INSTRUCTIONAL YEAR LEVEL SUBJECT 14
        $student['INSTRUCTIONAL YEAR LEVEL SUBJECT 14'],
        //87 SUBJECT 15
        $student['SUBJECT 15'],
        //88 MODE OF INSTRUCTION SUBJECT 15
        $student['MODE OF INSTRUCTION SUBJECT 15'],
        //89 HOURS PER YEAR SUBJECT 15
        $student['HOURS PER YEAR SUBJECT 15'],
        //90 INSTRUCTIONAL YEAR LEVEL SUBJECT 15
        $student['INSTRUCTIONAL YEAR LEVEL SUBJECT 15'],
        //91 TUITION WEEKS
        $student['TUITION WEEKS'],
        //92 NON-NQF QUAL
        $student['NON-NQF QUAL'],
        //93 UE
        $student['UE'],
        //94 EXCHANGE SCHEME
        $student['EXCHANGE SCHEME'],
        //95 BOARDING STATUS
        $student['BOARDING STATUS'],
        //96 ADDRESS1
        $student['Address1'],
        //97 ADDRESS2
        $student['Address2'],
        //98 ADDRESS3
        $student['Address3'],
        //99 ADDRESS4
        $student['Address4'],
        //100 ELIGIBILITY CRITERIA
        $student['ELIGIBILITY CRITERIA'],
        //101 VERIFICATION DOCUMENT
        $student['VERIFICATION DOCUMENT'],
        //102 SERIAL NUMBER
        $student['SERIAL NUMBER'],
        //103 CURRENT YEAR LEVEL
        $student['current_year_level'],
        //104 POST-SCHOOL ACTIVITY
        $student['POST-SCHOOL ACTIVITY'],
        //105 PRIVACY INDICATOR
        $student['PRIVACY INDICATOR'],
        //106 MIDDLE NAME(S)
        $student['middle_name'],
        //107 PREFERRED FIRST NAME
        $student['preferred_name'],
        //108 PREFERRED LAST NAME
        $student['preferred_last_name'],
        //109 EXPIRY DATE
        $student['EXPIRY DATE'],
        //110 STP
        $student['STP'],
        //111 WITHHOLD CONTACT DETAILS
        $student['WITHHOLD CONTACT DETAILS'],
        //112 HOME PHONE DETAILS
        $student['Phone'],
        //113 CELL PHONE NUMBER
        $student['mobile_phone'],
        //114 ALTERNATIVE PHONE NUMBER
        $student['ALTERNATIVE PHONE NUMBER'],
        //115 EMAIL ADDRESS
        $student['email_address'],
        //116 CONTACT 1 SURNAME
        $student['contact_1_last_name'],
        //117 CONTACT 1 FIRSTNAME
        $student['contact_1_first_name'],
        //118 CONTACT 1 ADDRESS1
        $student['contact_1_address1'],
        //119 CONTACT 1 ADDRESS2
        $student['contact_1_address2'],
        //120 CONTACT 1 ADDRESS3
        $student['contact_1_address3'],
        //121 CONTACT 1 ADDRESS4
        $student['contact_1_address4'],
        //122 CONTACT 1 ADDRESS5
        $student['contact_1_address5'],
        //123 CONTACT 1 PHONE NUMBER
        $student['contact_1_mobile'],
        //124 CONTACT 2 SURNAME
        $student['contact_1_last_name'],
        //125 CONTACT 2 FIRSTNAME
        $student['contact_2_first_name'],
        //126 CONTACT 2 ADDRESS1
        $student['contact_2_address1'],
        //127 CONTACT 2 ADDRESS2
        $student['contact_2_address2'],
        //128 CONTACT 2 ADDRESS3
        $student['contact_2_address3'],
        //129 CONTACT 2 ADDRESS4
        $student['contact_2_address4'],
        //130 CONTACT 2 ADDRESS5
        $student['contact_2_address5'],
        //131 CONTACT 1=2 PHONE NUMBER
        $student['contact_2_mobile'],
        //132 STAR
        $student['STAR']
      ));
    }

    $nzdt = new DateTimeZone('Pacific/Auckland');
    $now = new DateTime('now', $nzdt);

    //Write Footer
    $moeFile->writeLine(array(
      'Footer',
      count($dataArray['students']),
      $now->format('Ymd'),
      $now->format('Hi'),
      $dataArray['meta']['approver'],
      $now->format('Ymd'),
      $now->format('Hi')
    ));

    //M3 Table
    $moeFile->writeLine(array(
      'FR',
      $m3Data['FR']['M']['1'],
      $m3Data['FR']['M']['2'],
      $m3Data['FR']['M']['3'],
      $m3Data['FR']['M']['4'],
      $m3Data['FR']['M']['5'],
      $m3Data['FR']['M']['6'],
      $m3Data['FR']['M']['7'],
      $m3Data['FR']['M']['8'],
      $m3Data['FR']['M']['9'],
      $m3Data['FR']['M']['10'],
      $m3Data['FR']['M']['11'],
      $m3Data['FR']['M']['12'],
      $m3Data['FR']['M']['13'],
      $m3Data['FR']['M']['14'],
      $m3Data['FR']['M']['15'],
      $m3Data['FR']['F']['1'],
      $m3Data['FR']['F']['2'],
      $m3Data['FR']['F']['3'],
      $m3Data['FR']['F']['4'],
      $m3Data['FR']['F']['5'],
      $m3Data['FR']['F']['6'],
      $m3Data['FR']['F']['7'],
      $m3Data['FR']['F']['8'],
      $m3Data['FR']['F']['9'],
      $m3Data['FR']['F']['10'],
      $m3Data['FR']['F']['11'],
      $m3Data['FR']['F']['12'],
      $m3Data['FR']['F']['13'],
      $m3Data['FR']['F']['14'],
      $m3Data['FR']['F']['15']
    ));
    $moeFile->writeLine(array(
      'PR',
      $m3Data['PR']['M']['9'],
      $m3Data['PR']['M']['10'],
      $m3Data['PR']['M']['11'],
      $m3Data['PR']['M']['12'],
      $m3Data['PR']['M']['13'],
      $m3Data['PR']['M']['14'],
      $m3Data['PR']['M']['15'],
      $m3Data['PR']['F']['9'],
      $m3Data['PR']['F']['10'],
      $m3Data['PR']['F']['11'],
      $m3Data['PR']['F']['12'],
      $m3Data['PR']['F']['13'],
      $m3Data['PR']['F']['14'],
      $m3Data['PR']['F']['15']
    ));
    $moeFile->writeLine(array(
      'FA',
      $m3Data['FA']['M']['9'],
      $m3Data['FA']['M']['10'],
      $m3Data['FA']['M']['11'],
      $m3Data['FA']['M']['12'],
      $m3Data['FA']['M']['13'],
      $m3Data['FA']['M']['14'],
      $m3Data['FA']['M']['15'],
      $m3Data['FA']['F']['9'],
      $m3Data['FA']['F']['10'],
      $m3Data['FA']['F']['11'],
      $m3Data['FA']['F']['12'],
      $m3Data['FA']['F']['13'],
      $m3Data['FA']['F']['14'],
      $m3Data['FA']['F']['15']
    ));
    $moeFile->writeLine(array(
      'PA',
      $m3Data['PA']['M']['9'],
      $m3Data['PA']['M']['10'],
      $m3Data['PA']['M']['11'],
      $m3Data['PA']['M']['12'],
      $m3Data['PA']['M']['13'],
      $m3Data['PA']['M']['14'],
      $m3Data['PA']['M']['15'],
      $m3Data['PA']['F']['9'],
      $m3Data['PA']['F']['10'],
      $m3Data['PA']['F']['11'],
      $m3Data['PA']['F']['12'],
      $m3Data['PA']['F']['13'],
      $m3Data['PA']['F']['14'],
      $m3Data['PA']['F']['15']
    ));
    $moeFile->writeLine(array(
      'ST',
      $m3Data['ST']['M']['9'],
      $m3Data['ST']['M']['10'],
      $m3Data['ST']['M']['11'],
      $m3Data['ST']['M']['12'],
      $m3Data['ST']['M']['13'],
      $m3Data['ST']['M']['14'],
      $m3Data['ST']['M']['15'],
      $m3Data['ST']['F']['9'],
      $m3Data['ST']['F']['10'],
      $m3Data['ST']['F']['11'],
      $m3Data['ST']['F']['12'],
      $m3Data['ST']['F']['13'],
      $m3Data['ST']['F']['14'],
      $m3Data['ST']['F']['15']
    ));
    $moeFile->writeLine(array(
      'AE',
      $m3Data['AE']['M']['9'],
      $m3Data['AE']['M']['10'],
      $m3Data['AE']['M']['11'],
      $m3Data['AE']['M']['12'],
      $m3Data['AE']['M']['13'],
      $m3Data['AE']['M']['14'],
      $m3Data['AE']['M']['15'],
      $m3Data['AE']['F']['9'],
      $m3Data['AE']['F']['10'],
      $m3Data['AE']['F']['11'],
      $m3Data['AE']['F']['12'],
      $m3Data['AE']['F']['13'],
      $m3Data['AE']['F']['14'],
      $m3Data['AE']['F']['15']
    ));
    $moeFile->writeLine(array(
      'FF',
      $m3Data['FF']['M']['1'],
      $m3Data['FF']['M']['2'],
      $m3Data['FF']['M']['3'],
      $m3Data['FF']['M']['4'],
      $m3Data['FF']['M']['5'],
      $m3Data['FF']['M']['6'],
      $m3Data['FF']['M']['7'],
      $m3Data['FF']['M']['8'],
      $m3Data['FF']['M']['9'],
      $m3Data['FF']['M']['10'],
      $m3Data['FF']['M']['11'],
      $m3Data['FF']['M']['12'],
      $m3Data['FF']['M']['13'],
      $m3Data['FF']['M']['14'],
      $m3Data['FF']['M']['15'],
      $m3Data['FF']['F']['1'],
      $m3Data['FF']['F']['2'],
      $m3Data['FF']['F']['3'],
      $m3Data['FF']['F']['4'],
      $m3Data['FF']['F']['5'],
      $m3Data['FF']['F']['6'],
      $m3Data['FF']['F']['7'],
      $m3Data['FF']['F']['8'],
      $m3Data['FF']['F']['9'],
      $m3Data['FF']['F']['10'],
      $m3Data['FF']['F']['11'],
      $m3Data['FF']['F']['12'],
      $m3Data['FF']['F']['13'],
      $m3Data['FF']['F']['14'],
      $m3Data['FF']['F']['15']
    ));

    //M4 Table
    $moeFile->writeLine(array(
      'MLL1',
      $m4Data['MLL1']['total']['1'],
      $m4Data['MLL1']['total']['2'],
      $m4Data['MLL1']['total']['3'],
      $m4Data['MLL1']['total']['4'],
      $m4Data['MLL1']['total']['5'],
      $m4Data['MLL1']['total']['6'],
      $m4Data['MLL1']['total']['7'],
      $m4Data['MLL1']['total']['8'],
      $m4Data['MLL1']['total']['9'],
      $m4Data['MLL1']['total']['10'],
      $m4Data['MLL1']['total']['11'],
      $m4Data['MLL1']['total']['12'],
      $m4Data['MLL1']['total']['13'],
      $m4Data['MLL1']['total']['14'],
      $m4Data['MLL1']['total']['15']
    ));
    $moeFile->writeLine(array(
      'MLL2',
      $m4Data['MLL2']['total']['1'],
      $m4Data['MLL2']['total']['2'],
      $m4Data['MLL2']['total']['3'],
      $m4Data['MLL2']['total']['4'],
      $m4Data['MLL2']['total']['5'],
      $m4Data['MLL2']['total']['6'],
      $m4Data['MLL2']['total']['7'],
      $m4Data['MLL2']['total']['8'],
      $m4Data['MLL2']['total']['9'],
      $m4Data['MLL2']['total']['10'],
      $m4Data['MLL2']['total']['11'],
      $m4Data['MLL2']['total']['12'],
      $m4Data['MLL2']['total']['13'],
      $m4Data['MLL2']['total']['14'],
      $m4Data['MLL2']['total']['15']
    ));
    $moeFile->writeLine(array(
      'MLL3',
      $m4Data['MLL3']['total']['1'],
      $m4Data['MLL3']['total']['2'],
      $m4Data['MLL3']['total']['3'],
      $m4Data['MLL3']['total']['4'],
      $m4Data['MLL3']['total']['5'],
      $m4Data['MLL3']['total']['6'],
      $m4Data['MLL3']['total']['7'],
      $m4Data['MLL3']['total']['8'],
      $m4Data['MLL3']['total']['9'],
      $m4Data['MLL3']['total']['10'],
      $m4Data['MLL3']['total']['11'],
      $m4Data['MLL3']['total']['12'],
      $m4Data['MLL3']['total']['13'],
      $m4Data['MLL3']['total']['14'],
      $m4Data['MLL3']['total']['15']
    ));
    $moeFile->writeLine(array(
      'MLL4A',
      $m4Data['MLL4A']['total']['1'],
      $m4Data['MLL4A']['total']['2'],
      $m4Data['MLL4A']['total']['3'],
      $m4Data['MLL4A']['total']['4'],
      $m4Data['MLL4A']['total']['5'],
      $m4Data['MLL4A']['total']['6'],
      $m4Data['MLL4A']['total']['7'],
      $m4Data['MLL4A']['total']['8'],
      $m4Data['MLL4A']['total']['9'],
      $m4Data['MLL4A']['total']['10'],
      $m4Data['MLL4A']['total']['11'],
      $m4Data['MLL4A']['total']['12'],
      $m4Data['MLL4A']['total']['13'],
      $m4Data['MLL4A']['total']['14'],
      $m4Data['MLL4A']['total']['15']
    ));
    $moeFile->writeLine(array(
      'MLL4B',
      $m4Data['MLL4B']['total']['1'],
      $m4Data['MLL4B']['total']['2'],
      $m4Data['MLL4B']['total']['3'],
      $m4Data['MLL4B']['total']['4'],
      $m4Data['MLL4B']['total']['5'],
      $m4Data['MLL4B']['total']['6'],
      $m4Data['MLL4B']['total']['7'],
      $m4Data['MLL4B']['total']['8'],
      $m4Data['MLL4B']['total']['9'],
      $m4Data['MLL4B']['total']['10'],
      $m4Data['MLL4B']['total']['11'],
      $m4Data['MLL4B']['total']['12'],
      $m4Data['MLL4B']['total']['13'],
      $m4Data['MLL4B']['total']['14'],
      $m4Data['MLL4B']['total']['15']
    ));
    $moeFile->writeLine(array(
      'MLL5',
      $m4Data['MLL5']['total']['1'],
      $m4Data['MLL5']['total']['2'],
      $m4Data['MLL5']['total']['3'],
      $m4Data['MLL5']['total']['4'],
      $m4Data['MLL5']['total']['5'],
      $m4Data['MLL5']['total']['6'],
      $m4Data['MLL5']['total']['7'],
      $m4Data['MLL5']['total']['8'],
      $m4Data['MLL5']['total']['9'],
      $m4Data['MLL5']['total']['10'],
      $m4Data['MLL5']['total']['11'],
      $m4Data['MLL5']['total']['12'],
      $m4Data['MLL5']['total']['13'],
      $m4Data['MLL5']['total']['14'],
      $m4Data['MLL5']['total']['15']
    ));
    $moeFile->writeLine(array(
      'MLL6',
      $m4Data['MLL6']['total']['1'],
      $m4Data['MLL6']['total']['2'],
      $m4Data['MLL6']['total']['3'],
      $m4Data['MLL6']['total']['4'],
      $m4Data['MLL6']['total']['5'],
      $m4Data['MLL6']['total']['6'],
      $m4Data['MLL6']['total']['7'],
      $m4Data['MLL6']['total']['8'],
      $m4Data['MLL6']['total']['9'],
      $m4Data['MLL6']['total']['10'],
      $m4Data['MLL6']['total']['11'],
      $m4Data['MLL6']['total']['12'],
      $m4Data['MLL6']['total']['13'],
      $m4Data['MLL6']['total']['14'],
      $m4Data['MLL6']['total']['15']
    ));

    return $moeFile->getPath();
  }

  /**
   * Returns true if the students FIRST ATTENDANCE is before the collection date
   * and LAST ATTENDANCE is null or after the collection date
   * @param  DateTime $collectionDate
   * @param  Array $student
   * @return boolean
   */
  private static function studentAttendingForDate($collectionDate, $student) {
    $nzdt = new DateTimeZone('Pacific/Auckland');
    $startDate = new DateTime($student['start_date'], $nzdt);
    $lastAttendance = empty($student['LAST ATTENDANCE']) ? null : new DateTime($student['LAST ATTENDANCE'], $nzdt);
    return ($startDate->getTimestamp() <= $collectionDate->getTimestamp() &&
      (is_null($lastAttendance) || $lastAttendance->getTimestamp() >= $collectionDate->getTimestamp));
  }

  /**
   * Returns the collection date for the given month code and year
   * @param  String $collectionMonth Collection month code 
   * @param  String $collectionYear  Collection year
   * @return DateTime
   */
  private static function collectionDate($collectionMonth, $collectionYear) {
    $collectionDate;
    $nzdt = new DateTimeZone('Pacific/Auckland');
    switch ($collectionMonth) {
      case 'M':
        // and FIRST ATTENDANCE is <=1 March 2015 or Roll count day
        // and LAST ATTENDANCE is Null or >=1 March 2015 or roll count day
        $collectionDate = new DateTime($collectionYear . '-03-01', $nzdt);
        break;
      case 'E':
        //The specs for Table E3 have an error. The cut-off date is 28 May 2015.
        $collectionDate = new DateTime($collectionYear . '-05-28');
        break;
      case 'J':
        // and FIRST ATTENDANCE is <= 1 July 2015
        // and LAST ATTENDANCE is Null or >=1 July2015
        $collectionDate = new DateTime($collectionYear . '-07-01');
        break;
      case 'S':
        // and FIRST ATTENDANCE is <=2 September 2015 or Roll count day
        // and LAST ATTENDANCE is Null or >=2 September 2015 or roll count day
        $collectionDate = new DateTime($collectionYear . '-09-02');
        break;
    }
    return $collectionDate;
  }

  /**
   * Calculates the FTE for students in type FF, EX, AE, RA, AD ,RE, TPREOM and TPRAOM
   * who have FIRST ATTENDANCE before march first of collection year and last attendance null
   * or after march first of colleciton year
   *
   * Based on total for table M3, E3, J3 or S3 depending on collection month
   * @param  String $collectionMonth
   * @param  String $collectionYear
   * @param  Array  $studentArray
   * @return String FTE Total
   */
  private static function calculateSchoolRollByType($collectionMonth, $collectionYear, $studentArray) {

    /**
     * Returns true if student type is valid for counting roll
     * and student start and end dates are valid for given collection date
     * @param  DateTime $collectionDate
     * @param  Array    $student
     * @return boolean
     */
    $studentFilter = function($collectionDate, $student) {
      $validStudentTypes = array('FF', 'EX', 'AE', 'RA', 'AD', 'RE', 'TPREOM', 'TPRAOM');
      return (in_array($student['TYPE'], $validStudentTypes) &&
        self::studentAttendingForDate($collectionDate, $student));
    };


    $m3Data = array(
      'total' => '0'
    );

    //Columns (student types)
    //FR - Number of Full Time Regular
    //PR - FTE of Part Time Regular
    //FA - Full Time Adult
    //PA - Part Time Adult
    //ST - Secondary Tertiary Program
    //AE - Alternative Education
    //FF - International Fee Paying

    //Fill initial totals with 0
    $collectedTypes = ['FR', 'PR', 'FA', 'PA', 'ST', 'AE', 'FF'];
    foreach ($collectedTypes as $type) {
      $m3Data[$type] = array(
        'M' => array(
        ),
        'F' => array(
        )
      );
      for ($i = 9; $i <= 15; $i++) {
        $m3Data[$type]['M'][$i] = '0';
        $m3Data[$type]['F'][$i] = '0';
      }
    }
    for ($i = 1; $i <= 8; $i++) {
      $m3Data['FR']['M'][$i] = '0';
      $m3Data['FR']['F'][$i] = '0';
      $m3Data['FF']['M'][$i] = '0';
      $m3Data['FF']['F'][$i] = '0';
    }

    $collectionDate = self::collectionDate($collectionMonth, $collectionYear);
    $nzdt = new DateTimeZone('Pacific/Auckland');
    $january1 = new DateTime($collectionYear . '-01-01', $nzdt);

    $stpList = ['1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22'];

    foreach ($studentArray as $student) {

      if ($studentFilter($collectionDate, $student)) {

        $yearLevel = $student['funding_year_level'];
        $gender = $student['gender'];
        $dob = new DateTime($student['dob'], $nzdt);
        $ageAtJan1 = $dob->diff($january1)->y;

        // Students with STP in (1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22) 
        // should be reported in the FTE of Secondary Tertiary Programme Students 
        // column, unless student type is FF or AE.
        if ($student['TYPE'] === 'FF') {
          $column = 'FF';
        } else if ($student['TYPE'] === 'AE') {
          $column = 'AE';
        } else if (in_array($student['STP'], $stpList)) {
          $column = 'ST';
        } else {
          if ($ageAtJan1 < 19) {
            //Regular
            if (bccomp($student['FTE'], '1', 1) === 0) {
              //Full time
              $column = 'FR';
            } else {
              //Part time
              $column = 'PR';
            }
          } else {
            //Adult
            if (bccomp($student['FTE'], '1', 1) === 0) {
              //Full time
              $column = 'FA';
            } else {
              //Part time
              $column = 'PA';
            }
          }
        }

        $cell = bcadd($m3Data[$column][$gender][$yearLevel], $student['FTE'], 1);
        //Trim trailing .0
        if (substr($cell, -2) === '.0') {
          $cell = substr($cell, 0, strlen($cell) - 2);
        }
        $m3Data[$column][$gender][$yearLevel] = $cell;
        $m3Data['total'] = bcadd($m3Data['total'], $student['FTE'], 1);
      }
    }

    return $m3Data;
  }

  /**
   * Returns an array of highest level of maori language learning,
   * equivalent to tables M4, J7 etc
   * @param  String $collectionMonth ['M','E','J','S'] Used to calculate the cutoff date for roll collection
   * @param  String $collectionYear  Year used to calculate cutoff date for roll collection
   * @param  Array $students         Array of students included in roll
   * @return Array                   Array of totals indexed by maori level (MLL1 etc), 'total' or 'maori', then year level
   */
  private static function calculateHighestLevelMaori($collectionMonth, $collectionYear, $students) {
    $studentFilter = function($collectionDate, $student) {
      // Student TYPE in [EX, RA, AD, RE, TPREOM, TPRAOM]
      // and MĀORI=not Null
      $allowedTypes = ['EX', 'RA', 'AD', 'RE', 'TPREOM', 'TPRAOM'];
      return (in_array($student['TYPE'], $allowedTypes) &&
        !empty($student['MAORI']) &&
        self::studentAttendingForDate($collectionDate, $student));
    };

    $collectionDate = self::collectionDate($collectionMonth, $collectionYear);

    $m4Columns = array(
      'MLL1',
      'MLL2',
      'MLL3',
      'MLL4A',
      'MLL4B',
      'MLL5',
      'MLL6'
    );

    $m4Data = array();

    //Populate m4Data with 0 values
    foreach($m4Columns as $column) {
      $m4Data[$column] = array(
        'total' => array(),
        'maori' => array()
      );
      for ($i = 0; $i <= 15; $i++) {
        $m4Data[$column]['total'][$i] = 0;
        $m4Data[$column]['maori'][$i] = 0;
      }
    }
    foreach($students as $student) {
      if ($studentFilter($collectionDate, $student)) {
        $yearLevel = $student['funding_year_level'];
        //Column was being re-used here from last loop
        $column = null;
        switch ($student['MAORI']) {
          case ('H'):
            $column = 'MLL1';
            break;
          case ('G'):
            $column = 'MLL1';
            break;
          case ('F'):
            $column = 'MLL2';
            break;
          case ('E'):
            $column = 'MLL3';
            break;
          case ('D'):
            $column = 'MLL4A';
            break;
          case ('C'):
            $column = 'MLL4B';
            break;
          case ('B'):
            $column = 'MLL5';
            break;
          case ('A'):
            $column = 'MLL6';
            break;
        }
        //Maori students are counted separately
        if ($student['ethnic_origin'] == '211' ||
          $student['ethnic_origin2'] == '211' ||
          $student['ethnic_origin3'] == '211') {
          $m4Data[$column]['maori'][$yearLevel]++;
        }
        //All students regardless of race 
        $m4Data[$column]['total'][$yearLevel]++;
      }
    }

    return $m4Data;
  }
}
