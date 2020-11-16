<?php

namespace CodeChallenge\Form;

use CodeChallenge\File\FileManagement;
use CodeChallenge\Form\Form;
use CodeChallenge\Render\PageBuilder;

/**
 * Upload form for CSV.
 */
class Upload extends Form {

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
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'redcat-csv-upload';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm() {
    $original = $this->fileManager->originalFileExists();
    $altered = $this->fileManager->alteredFileExists();
    if ($original) {
      $message = 'Uploading a new data file will delete the <a href="/data/original/view">previous file</a>.';
      if ($altered) {
        $message = 'Uploading a new data file will delete the <a href="/data/original/view">previous file</a> and the <a href="/data/saved/view">altered file</a>.';
      }
      $form['warning'] = [
        '#type' => 'markup',
        '#markup' => '<p class="warning">' . $message . '</p>',
      ];
    }

    $form['#file_upload'] = TRUE;

    $form['headers'] = [
      '#type' => 'checkbox',
      '#title' => 'CSV has column headers',
      '#default_value' => 1,
      '#description' => 'If unchecked, basic column headers will be added. column header should only have alphabetical characters, dashes (-), and underscores (_).',
    ];

    $form['data_file'] = [
      '#type' => 'file',
      '#title' => 'Upload Data',
      '#extensions' => '.csv',
      '#description' => 'Only .csv files are accepted.',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#title' => 'Upload',
    ];

    return $form;
  }

  /**
   * Build form page.
   */
  public function render() {
    $build['content'] = $this->renderForm();
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

    $this->pageBuilder->buildPage($build);
  }

  /**
   * {@inheritdoc}
   */
  public function submit() {
    $values = $this->getValues();

    $error = FALSE;

    if (!empty($_FILES["data_file"]["name"])) {
      $ext = pathinfo($_FILES['data_file']['name'], PATHINFO_EXTENSION);
      if ($ext = 'csv') {
        $saved = move_uploaded_file($_FILES["data_file"]["tmp_name"], BASE_PATH . '/files/original.' . $ext);
        if ($saved) {
          if ($this->fileManager->alteredFileExists()) {
            $this->fileManager->deleteAlteredFile();
          }
          if (empty($values['headers'])) {
            $this->fileManager->addColumnHeaders();
          }
        }
        else {
          $GLOBALS['redcat_app_errors'][] = 'There was an error while saving your file. Please try again.';
        $error = TRUE;
        }
      }
      else {
        $GLOBALS['redcat_app_errors'][] = 'The uploaded file was not a CSV.';
        $error = TRUE;
      }
    }
    else {
      $GLOBALS['redcat_app_errors'][] = 'A problem occurred while uploading your file. Please try again.';
        $error = TRUE;
    }

    if ($error) {
      $this->render();
    }
    else {
      header('Location: /data/original/view');
    }
  }

}
