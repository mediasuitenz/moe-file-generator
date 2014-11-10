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
