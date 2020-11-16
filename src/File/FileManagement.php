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

}
