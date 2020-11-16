<?php

namespace CodeChallenge\File;

/**
 * Upload form for CSV.
 */
class FileManagement {

  /**
   * Check for original file.
   *
   * @return bool
   *   True is file exists.
   */
  public function originalFileExists() {
    return file_exists(BASE_PATH . '/files/original.csv');
  }

  /**
   * Check for altered file.
   *
   * @return bool
   *   True is file exists.
   */
  public function alteredFileExists() {
  	return file_exists(BASE_PATH . '/files/altered.csv');
  }

  /**
   * Delete altered file.
   */
  public function deleteAlteredFile() {
    unlink(BASE_PATH . '/files/altered.csv');
  }

  /**
   * CSV to array.
   *
   * @param string $csv
   *   CSV name to use.
   *
   * @return array
   *   Array of CSV contents.
   */
  public function csvToArray(string $csv) {
    $csv_array = [];
    if (($file = fopen(BASE_PATH . '/files/' . $csv . '.csv', 'r')) !== FALSE) {
      while (($data = fgetcsv($file, 1000, ',')) !== FALSE) {
        $csv_array[] = $data;
      }
      fclose($file);
    }
    else {
      $GLOBALS['redcat_app_errors'][] = 'The application failed to read ' . BASE_PATH . '/files/' . $csv . '.csv.';
    }

    return $csv_array;
  }

  /**
   * Array to CSV.
   *
   * @param string $csv
   *   CSV name to use.
   * @param array $data
   *   Data to write.
   */
  public function arrayToCsv(string $csv, array $data) {
    if (($file = fopen(BASE_PATH . '/files/' . $csv . '.csv', 'w')) !== FALSE) {
      foreach ($data as $fields) {
        fputcsv($file, $fields);
      }
      fclose($file);
    }
    else {
      $GLOBALS['redcat_app_errors'][] = 'The application failed to write to ' . BASE_PATH . '/files/' . $csv . '.csv.';
    }
  }

  /**
   * Add headers to uploaded data.
   */
  public function addColumnHeaders() {
    $csv = $this->csvToArray('original');
    $num = count(reset($csv));
    $headers = [];
    for ($i = 0; $i < $num; $i++) { 
      $headers[] = 'column_' . ($i + 1);
    }
    array_unshift($csv, $headers);
    $this->arrayToCsv('original', $csv);
  }

}
