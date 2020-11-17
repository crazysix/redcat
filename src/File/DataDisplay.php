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
   * Column form object.
   *
   * @var \CodeChallenge\Form\AddColumn
   */
  protected $columnForm;

  /**
   * File to use.
   */
  public $file = 'original';

  /**
   * Constructs the FileOptions object.
   */
  public function __construct($file) {
    $this->fileManager = new FileManagement();
    $this->columnForm = new AddColumn();
    $this->pageBuilder = new PageBuilder();
    // Only recognize original or altered.
    if ($file == 'altered') {
      $this->file = $file;
    }
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
    $build['title'] = 'Uploaded Data View';
    if ($this->file == 'altered') {
      $build['title'] = 'Altered Data View';
    }

    // Menu check.
    if ($this->fileManager->originalFileExists()) {
      $build['menu'][] = [
        'link' => '/data/original/view',
        'title' => 'View Original Data',
      ];
      if ($this->fileManager->alteredFileExists()) {
        $build['menu'][] = [
          'link' => '/data/altered/view',
          'title' => 'View Altered Data',
        ];
      }
    }

    // Add JS and CSS.
    $build['js'] = [
      '/js/jquery.dataTables.js',
    ];
    $build['css'] = [
      '/css/jquery.dataTables.css',
    ];

    $build['content'] = '<p>Your data is ready. '
      . 'Click "Add Column" to add a custom expression. Adding a column '
      . 'will save an altered data set. You can work with the original '
      . 'altered data.</p>';

    // Get file data.
    $data = $this->fileManager->csvToArray($this->file);

    // Get form build.
    $build['content'] .= '<div id="column-form">'
      . $this->columnForm->renderForm() . '</div>';

    // Build data table.
    $build['content'] .= '<table id="data-table" class="display">';
    $build['content'] .= '<thead><tr>';
    foreach ($data[0] as $column) {
      $build['content'] .= '<th>' . $column . '</th>';
    }
    $build['content'] .= '</tr></thead><tbody>';
    foreach ($data as $key => $row) {
      if ($key != 0) {
        $build['content'] .= '<tr>';
        foreach ($row as $field) {
          $build['content'] .= '<td>' . $field . '</td>';
        }
        $build['content'] .= '</tr>';
      }
    }
    $build['content'] .= '</tbody></table>';

    return $build;
  }

  /**
   * Post handler for column form.
   */
  public function post() {
    // Get file data.
    $data = $this->fileManager->csvToArray($this->file);

    // Set data.
    $this->columnForm->setData($data);

    // Execute form submit.
    $new_data = $this->columnForm->submit();

    if (!empty($new_data)) {
      // Always write to altered
      $this->fileManager->arrayToCsv('altered', $new_data);
      header('Location: /data/altered/view');
    }
    else {
      $this->build();
    }
  }

}
