<?php
putenv('ENVIRONMENT=TEST');

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR .
  '..' . DIRECTORY_SEPARATOR . 
  'MOEFileGenerator.php');

$testDir = dirname(__FILE__) . DIRECTORY_SEPARATOR .'moeFiles';

function moeDataArray() {
  return array(
    'meta' => array(
      'smsName' => 'Linc-Ed',
      'smsVersion' => '2.8',
      'schoolNumber' => '12345',
      'collectionMonth' => 'M',
      'collectionYear' => '2015',
      'enrolmentScheme' => 'N',
      'isDraft' => true
    ),
    'students' => array(
    )
  );
}

function readMOE($filePath) {
  $handle = fopen($filePath, 'rb');
  $moeContents = fread($handle, filesize($filePath));
  fclose($handle);
  return explode("\r\n", $moeContents);
}

class MOEFileGeneratorTest extends PHPUnit_Framework_TestCase {

  public static function setUpBeforeClass() {
    //Clean up .moe files from previous runs
    global $testDir;
    assert(is_dir($testDir));
    function rrmdir($dir) {
      if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
          if ($object !== '.' && $object !== '..') {
            $path = $dir . DIRECTORY_SEPARATOR . $object;
            rrmdir($path);
          }
        }
        reset($objects);
        rmdir($dir);
      } else {
        unlink($dir);
      }
    }
    $objects = scandir($testDir);
    foreach ($objects as $object) {
      if ($object !== '.' && $object !== '..' && $object !== '.gitignore') {
        rrmdir($testDir . DIRECTORY_SEPARATOR . $object);
      }
    }
  }

  public function testFileGenerator() {
    //When passed an array of data
    MOEFileGenerator\MOEFileGenerator::generateMOE(moeDataArray());

    //Expect a file to exist
    global $testDir;
    $filePath = $testDir . DIRECTORY_SEPARATOR .
      'DRAFT12345M15' . DIRECTORY_SEPARATOR .
      'v1' . DIRECTORY_SEPARATOR .
      'DRAFT12345M15.moe';
    $this->assertSame(is_file($filePath), true);

  }

  public function testMOEFileHeader() {

    global $testDir;

    $data = moeDataArray();
    $data['meta']['schoolNumber'] = '2';

    MOEFileGenerator\MOEFileGenerator::generateMOE($data);

    $fileName = $testDir . DIRECTORY_SEPARATOR .
    'DRAFT2M15' . DIRECTORY_SEPARATOR .
    'v1' . DIRECTORY_SEPARATOR .
    'DRAFT2M15.moe';

    $handle = fopen($fileName, 'rb');

    $moeContents = fread($handle, filesize($fileName));

    fclose($handle);

    // The end of the header line must contain both a Carriage Return(0d) and Line Feed(Oa) specifically the new line
    // character CRLF
    $this->assertSame((strpos($moeContents, "\r\n") >= 0), true);

    // The Header in the data file consists of one line and MUST include the following, separated by a comma: 

    $headerLine = explode("\r\n", $moeContents)[0];

    $header = explode(",", $headerLine);

    // the SMS Name
    $this->assertSame($header[0], 'Linc-Ed');

    // SMS Software version (e.g. v2015)
    $this->assertSame($header[1], '2.8');

    // Month and year of collection (e.g. M, 2015)
    $this->assertSame($header[2], 'M');
    $this->assertSame($header[3], '2015');

    // The school number
    $this->assertSame($header[4], '2');
    
    // The total number of students on the school roll (determined by the total for table M3, E3, J3 or S3 depending on 
    // return date)
    $this->assertSame(is_numeric($header[5]), true);
    
    // Enrolment Scheme (Y or N) and the Effective date of that scheme (as YYYYMMDD) – e.g. if School is participating in a 
    // Ministry approved Enrolment Scheme that became effective 23 August 2004; Y, 20040823. If the School was not 
    // participating in an Enrolment Scheme the file should read N,00000000.
    $this->assertSame(in_array($header[6], ['Y','N']), true);

    $this->assertSame($header[7], '00000000');
  }

  public function testFTETotalInHeader() {
    global $testDir;

    //Test that FTE is included
    $dataArray = moeDataArray();
    $dataArray['meta']['schoolNumber'] = '3';

    array_push($dataArray['students'], array(
      'FTE' => '1',
      'start_date' => '2008-08-29',
      'TYPE' => 'RE'
    ));
    MOEFileGenerator\MOEFileGenerator::generateMOE($dataArray);

    $fileName = $testDir . DIRECTORY_SEPARATOR .
    'DRAFT3M15' . DIRECTORY_SEPARATOR .
    'v1' . DIRECTORY_SEPARATOR .
    'DRAFT3M15.moe';

    $headerLine = readMOE($fileName)[0];

    $header = explode(",", $headerLine);

    $studentTotal = $header[5];

    $this->assertSame(in_array($studentTotal, array('1', '1.0')), true);

    //Add some FTE's together

    $dataArray['meta']['schoolNumber'] = '4';

    array_push($dataArray['students'], array(
      'FTE' => '0.8',
      'start_date' => '2008-08-30',
      'TYPE' => 'RE'
    ));

    array_push($dataArray['students'], array(
      'FTE' => '0.3',
      'start_date' => '2008-08-30',
      'TYPE' => 'RE'
    ));

    MOEFileGenerator\MOEFileGenerator::generateMOE($dataArray);

    $fileName = $testDir . DIRECTORY_SEPARATOR .
    'DRAFT4M15' . DIRECTORY_SEPARATOR .
    'v1' . DIRECTORY_SEPARATOR .
    'DRAFT4M15.moe';

    $headerLine = readMOE($fileName)[0];

    $header = explode(",", $headerLine);

    $studentTotal = $header[5];

    $this->assertSame($studentTotal, '2.1');

    //Test that only valid students are included
    $dataArray = moeDataArray();
    $dataArray['meta']['schoolNumber'] = '5';

    array_push($dataArray['students'], array(
      'FTE' => '1',
      'start_date' => '2020-08-29',
      'TYPE' => 'RE'
    ));

    MOEFileGenerator\MOEFileGenerator::generateMOE($dataArray);

    $fileName = $testDir . DIRECTORY_SEPARATOR .
    'DRAFT5M15' . DIRECTORY_SEPARATOR .
    'v1' . DIRECTORY_SEPARATOR .
    'DRAFT5M15.moe';

    $headerLine = readMOE($fileName)[0];

    $header = explode(",", $headerLine);

    $studentTotal = $header[5];

    $this->assertSame(in_array($studentTotal, array('0', '0.0')), true);
  }

}
