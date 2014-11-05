<?php
putenv('ENVIRONMENT=TEST');

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR .
  '..' . DIRECTORY_SEPARATOR . 
  'MOEFileGenerator.php');

$testDir = dirname(__FILE__) . DIRECTORY_SEPARATOR .'moeFiles';

function moeDataArray() {
  return array(
    'meta' => array(
      'schoolNumber' => '12345',
      'collectionMonth' => 'M',
      'collectionYear' => '15',
      'isDraft' => true
    ),
    'students' => array(
    )
  );
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
    // The Header in the data file consists of one line and MUST include the following, separated by a comma: 
    // the SMS Name
    // SMS Software version (e.g. v2015)
    // Month and year of collection (e.g. M, 2015)
    // The school number
    // The total number of students on the school roll (determined by the total for table M3, E3, J3 or S3 depending on 
    // return date)
    // Enrolment Scheme (Y or N) and the Effective date of that scheme (as YYYYMMDD) – e.g. if School is participating in a 
    // Ministry approved Enrolment Scheme that became effective 23 August 2004; Y, 20040823. If the School was not 
    // participating in an Enrolment Scheme the file should read N,00000000.
    // The end of the header line must contain both a Carriage Return(0d) and Line Feed(Oa) specifically the new line
    // character CRLF

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
  }

}
