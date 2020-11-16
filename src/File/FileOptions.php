<?php

namespace CodeChallenge\File;

use CodeChallenge\File\FileManagement;
use CodeChallenge\Render\PageBuilder;

/**
 * Default file options.
 *
 * Used as application homepage.
 */
class FileOptions {

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
    $build['content'] = '';
    if (!$this->fileManager->originalFileExists()) {
      $build['content'] .= '<p>Welcome to the RedCat Systems coding challenge.</p>';
      $build['content'] .= '</p>Please <a href="/upload">upload a CSV</a> to begin.</p>';
    }
    else {
      $build['menu'][] = [
        'link' => '/data/original/view',
        'title' => 'View Original Data',
      ];
      $build['content'] .= '</p>Welcome back to the RedCat Systems coding challenge.</p>';
      $build['content'] .= '<p>You have previsously uploaded a CSV. You may:</p>';
      $build['content'] .= '<ul>';
      $build['content'] .= '<li><a href="/upload">Upload new data from a new CSV</a>';
      $build['content'] .= ' (doing this will delete your previously uploaded data).</li>';
      $build['content'] .= '<li><a href="/data/original/view">Work with your previously uploaded data</a>';
      if ($this->fileManager->alteredFileExists()) {
        $build['menu'][] = [
          'link' => '/data/altered/view',
          'title' => 'View Altered Data',
        ];
        $build['content'] .= ' (this will override your previously altered and saved data).</li>';
        $build['content'] .= '<li><a href="/data/altered/view">Work with your previously uploaded and altered data</a>';
      }
      $build['content'] .= '.</li></ul>';
    }

    return $build;
  }

}
