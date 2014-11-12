<?php

namespace MOEFileGenerator;

class MOEFile {

  private $filePath;
  private $month;
  private $year;
  private $isDraft;

  public function __construct(
    $schoolNumber, 
    $collectionMonth, 
    $collectionYear, 
    $isDraft,
    $baseDirectory) {

    //Base directory must exist
    assert(is_dir($baseDirectory));

    $fileName = '';
    if ($isDraft === true) {
      $fileName .= 'DRAFT';
    }
    $year = substr($collectionYear, 2);
    $fileName .= $schoolNumber . $collectionMonth . $year;

    if (!is_dir($baseDirectory . DIRECTORY_SEPARATOR . $fileName)) {
      mkdir($baseDirectory . DIRECTORY_SEPARATOR . $fileName);
    }

    //Insert a new record into the db and return the version
    $version = $this->createVersionRecord($schoolNumber, $collectionMonth, $collectionYear, $isDraft, $baseDirectory);

    //Should not be creating the same version twice
    assert(!is_dir($baseDirectory .
      DIRECTORY_SEPARATOR . $fileName .
      DIRECTORY_SEPARATOR . 'v' . $version), '.moe version should not already exist');

    //Create a directory representing this version of the .moe
    mkdir($baseDirectory . 
      DIRECTORY_SEPARATOR . $fileName .
      DIRECTORY_SEPARATOR . 'v' . $version);

    //Construct the full filepath for this .moe file
    $this->filePath = $this->buildFilePath($schoolNumber, $collectionMonth, $collectionYear, $isDraft, $version, $baseDirectory);

    //Create an empty file
    touch($this->filePath);
  }

  /**
   * Inserts a new record into the db for this .moe and returns the
   * version number
   * @param  String  $schoolNumber  School number
   * @param  String  $month         Collection month
   * @param  String  $year          Collection year
   * @param  boolean $isDraft       True if this is a draft .moe
   * @param  String  $baseDirectory Path .moe files are stored at
   * @return int
   */
  private function createVersionRecord($schoolNumber, $month, $year, $isDraft, $baseDirectory) {

    //Find or create a RollReturnPeriod in db
    $db = DBUtil::getConnection();

    $findRollReturnSql = 'SELECT * FROM `roll_return_period` WHERE ' .
      '`month` = :month AND `year` = :year AND `mode` = :mode';

    $mode = $isDraft ? 'DRAFT' : 'OFFICIAL';

    $findRollReturnBind = array(
      'month' => $month,
      'year' => $year,
      'mode' => $mode
    );

    $returnPeriod = $db->fetchOne($findRollReturnSql, $findRollReturnBind);

    assert($returnPeriod !== false, 'Return period not found');

    //Lock the db
    $db->exec('LOCK TABLES moe_file WRITE');

    //Get highest existing version for roll return period
    $lastFileSql = 'SELECT * FROM moe_file WHERE roll_return_period_id = :roll_return_period_id ORDER BY version DESC';
    $lastFile = $db->fetchOne($lastFileSql, array('roll_return_period_id' => $returnPeriod['id']));

    if ($lastFile !== false) {
     //Increment version
      $version = $lastFile['version'] + 1;
    } else {
      //If none exists version is 1
      $version = '1';
    }

    //Get the file path (possible now we have version number);
    $filePath = $this->buildFilePath($schoolNumber, $month, $year, $isDraft, $version, $baseDirectory);

    //Insert new row for this file
    $insertSql = 'INSERT INTO `moe_file` (`roll_return_period_id`, `version`, `file_path`) ' .
      'VALUES (:roll_return_period_id, :version, :file_path)';

    $db->perform($insertSql, array(
      'roll_return_period_id' => $returnPeriod['id'],
      'version' => $version,
      'file_path' => $filePath
    ));

    //Unlock db
    $db->exec('UNLOCK TABLES');
    return $version;
  }

  /**
   * Constructs the file path for a .moe file in the
   * format basePath/12345M14DRAFT/v1/12345M14DRAFT.moe
   * @param  String  $schoolNumber  School number
   * @param  String  $month         Collection month
   * @param  String  $year          Collection year
   * @param  boolean $isDraft       True if this is a draft .moe
   * @param  String  $version       Version of .moe
   * @param  String  $baseDirectory Base directory that .moe files are stored in
   * @return String
   */
  private function buildFilePath($schoolNumber, $month, $year, $isDraft, $version, $baseDirectory) {

    $fileName = '';
    if ($isDraft === true) {
      $fileName .= 'DRAFT';
    }
    $year = substr($year, 2);
    $fileName .= $schoolNumber . $month . $year;

    return $baseDirectory .
      DIRECTORY_SEPARATOR . $fileName .
      DIRECTORY_SEPARATOR . 'v' . $version .
      DIRECTORY_SEPARATOR . $fileName . '.moe';
  }

  /**
   * Writes an array of values to the .moe file as a line
   * Replaces occurrences of " with ""
   * Wraps values in " if a , appears in the value
   * Ends with line with CRLF (\r\n);
   * @param  [type] $rowArray [description]
   * @return [type]           [description]
   */
  public function writeLine($rowArray) {

    //Append lines to the csv
    $handle = fopen($this->filePath, 'a');

    $cells = array();
    //Because we can't control the line endings with fputcsv we need to
    //build the csv rows by hand
    foreach ($rowArray as $cell) {

      //Replace any occurrences of a quote with two quotes
      $cell = str_replace('"', '""', $cell);

      //Wrap in quotes if a comma or quote appears
      if (strpos($cell, ',') !== false || strpos($cell, '"') !== false) {
        $cell = '"' . $cell . '"';
      }
      array_push($cells, $cell);
    }
    $string = implode($cells, ',');
    //Enforce CRLF line ending
    $string .= "\r\n";
    fwrite($handle, $string);
    fclose($handle);
  }

  public function getPath() {
    return $this->filePath;
  }

}
