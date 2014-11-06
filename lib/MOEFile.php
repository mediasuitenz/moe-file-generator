<?php

namespace MOEFileGenerator;

class MOEFile {

  private $filePath;

  public function __construct(
    $schoolNumber, 
    $collectionMonth, 
    $collectionYear, 
    $isDraft, 
    $version,
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

    //Should not be creating the same version twice
    assert(!is_dir($baseDirectory .
      DIRECTORY_SEPARATOR . $fileName .
      DIRECTORY_SEPARATOR . 'v' . $version), '.moe version should not already exist');

    //Create a directory representing this version of the .moe
    mkdir($baseDirectory . 
      DIRECTORY_SEPARATOR . $fileName .
      DIRECTORY_SEPARATOR . 'v' . $version);

    $this->filePath = $baseDirectory .
      DIRECTORY_SEPARATOR . $fileName .
      DIRECTORY_SEPARATOR . 'v' . $version .
      DIRECTORY_SEPARATOR . $fileName . '.moe';

    touch($this->filePath);
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
