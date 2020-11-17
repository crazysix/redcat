<?php

namespace CodeChallenge\File;

use CodeChallenge\File\FileManagement;
use CodeChallenge\Form\AddColumn;
use CodeChallenge\Render\PageBuilder;

/**
 * Display CSV data.
 */
class DataDisplay {

  /**
   * The file manager class.
   *
   * @var \CodeChallenge\File\FileManagement
   */
  protected $fileManager;

  /**
   * The page builder.
   *
   * @var \CodeChallenge\Render\PageBuilder
   */
  protected $pageBuilder;

  /**
   * Constructs the FileOptions object.
   */
  public function __construct() {
    $this->fileManager = new FileManagement();
    $this->pageBuilder = new PageBuilder();
  }

  /**
   * Prepare Data options content.
   */
  public function build() {
    // Get build array.
    $build = $this->buildContent();

    // Build page.
    $this->pageBuilder->buildPage($build);
  }

  /**
   * Prepare Data options content.
   *
   * @return array
   *   The build array used for page construction.
   */
  public function buildContent() {
    $build['content'] = '<p>This page is in progress</p>';

    return $build;
  }

}
