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
    $this->add('/upload', [$this, 'buildUpload'], 'post');
    $this->add('/data/([a-z]+)/view', [$this, 'buildDataView']);
    $this->add('/data/([a-z]+)/view', [$this, 'buildDataView'], 'post');
  }

  /**
   * Build homepage.
   */
  public function buildHome() {
    $page = new \CodeChallenge\File\FileOptions();
    $page->build();
  }

  /**
   * Build upload form.
   *
   * @param string $method
   *   Method used.
   */
  public function buildUpload($method) {
    $form = new \CodeChallenge\Form\Upload();
    if ($method == 'post') {
      $form->submit();
    }
    else {
      $form->render();
    }
  }

  /**
   * Build data view.
   *
   * @param string $method
   *   Method used.
   * @param string $data
   *   Data view option.
   */
  public function buildDataView($method, $data) {
    echo 'Dynamic ' . $data . ' file.';
  }

}
