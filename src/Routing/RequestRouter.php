<?php

namespace CodeChallenge\Routing;

use CodeChallenge\Routing\Route;

/**
 * Router Request class.
 */
class RequestRouter extends Route {
  
  /**
   * Constructs the RequestRouter object.
   */
  public function __construct() {
    $this->add('/', [$this, 'buildHome']);
    $this->add('/upload', [$this, 'buildUpload']);
    $this->add('/data/([a-z]+)/view', [$this, 'buildDataView']);
    $this->add('/upload/submit', [$this, 'buildUpload'], 'post');
  }

  /**
   * Build homepage.
   */
  public function buildHome() {
    $page = new \CodeChallenge\File\FileOptions();
    $page->build();
  }

  /**
   * Build test.
   */
  public function buildUpload() {
    echo 'Dynamic test page.';
  }

  /**
   * Build data view.
   *
   * @param string $data
   *   Data view option.
   */
  public function buildDataView($data) {
    echo 'Dynamic ' . $data . ' file.';
  }

}
