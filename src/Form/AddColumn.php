<?php

namespace CodeChallenge\Form;

use CodeChallenge\Form\Form;

/**
 * AddColumn form for CSV.
 */
class AddColumn extends Form {

  /**
   * The csv data.
   *
   * @var array
   */
  protected $data = [];

  /**
   * Error tracking.
   *
   * @var bool
   */
  protected $error = FALSE;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'redcat-csv-add-column';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm() {
    $values = $this->getValues();

    $column = $this->error && !empty($values['column_name']) ? $values['column_name'] : '';
    $expression = $this->error && !empty($values['column_expression']) ? $values['column_expression'] : '';

    $form['column_name'] = [
      '#type' => 'textfield',
      '#title' => 'Column Name',
      '#description' => 'Column headers must have alphabetical characters. '
        . 'Dashes (-), and underscores (_), and numeric characters are okay.',
      '#default_value' => $column,
    ];

    $form['column_expression'] = [
      '#type' => 'textfield',
      '#title' => 'Expression',
      '#description' => 'Acceptable values include column name with no spaces, '
        . 'text with double quotes(""), concatenate (&), and arithimitic (+ - * /).',
      '#size' => 120,
      '#maxlength' => 512,
      '#default_value' => $expression,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#title' => 'Add Column',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $values = []) {
    if (empty($values['column_expression']) || empty($values['column_name'])) {
      $this->setError(TRUE);
      $GLOBALS['redcat_app_errors'][] = 'The column name and expression are required.';
      return FALSE;
    }

    // Check for even number of quotes.
    $quote_num = substr_count($values['column_expression'], '"');
    if (!($quote_num % 2 == 0)) {
      $this->setError(TRUE);
      $GLOBALS['redcat_app_errors'][] = 'The expression has an open quote.';
      return FALSE;
    }

    // Validate column name.
    if (preg_match('/^[a-zA-Z0-9_-]+$/', $values['column_name']) == 0) {
      $this->setError(TRUE);
      $GLOBALS['redcat_app_errors'][] = 'The column name has illegal characters. '
        . 'Please only use alpha, numeric, underscore (_), and dashes(-).';
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   *
   * @return mixed
   *   Array if successful, FALSE if an issue occurs.
   */
  public function submit() {
    $values = $this->getValues();

    if (!$this->validate($values)) {
      return FALSE;
    }

    if (!$this->error) {
      $expression = $values['column_expression'];
      $process = $this->parseExpression($expression);
      if ($process === FALSE) {
        return FALSE;
      }
      $data = $this->createData($process, $values['column_name']);
      if ($data) {
        $this->setData($data);
      }
      return $data;
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getValues(string $key = '') {
    // Fix quote formatting issue.
    if (isset($_POST['column_expression'])) {
      $_POST['column_expression'] = str_replace(
        ['”', '“'],
        '"',
        $_POST['column_expression']
      );
    }
    if (!empty($key)) {
      return $_POST[$key] ?? NULL;
    }

    return $_POST ?? [];
  }

  /**
   * Set CSV data.
   *
   * @param array $data
   *   The data from the CSV.
   */
  public function setData(array $data = []) {
    $this->data = $data;
    // Remove no-break space coming from csv.
    if (!empty($this->data[0])) {
      foreach ($this->data[0] as $key => $value) {
        $this->data[0][$key] = trim(
          urldecode(str_replace('%EF%BB%BF', '', urlencode($value)))
        );
      }
    }
  }

  /**
   * Set error status.
   *
   * @param bool $status
   *   The error status.
   */
  public function setError(bool $status = FALSE) {
    $this->error = $status;
  }

  /**
   * Parse Expression.
   *
   * This could be easy with eval() but that introduces security issues.
   *
   * @param string $expression
   *   The user submitted expression.
   *
   * @return mixed
   *   Return a processing array or FALSE on failure.
   */
  public function parseExpression(string $expression) {
    $process = [];

    preg_match_all('/"([^"]+)"/', $expression, $string_results);
    if (!empty($string_results[0])) {
      $expression_split = $expression;
      foreach ($string_results[0] as $key => $string) {
        $parse = explode($string, $expression_split, 2);
        if (strlen(trim($parse[0])) > 0) {
          $pre_expression = trim($parse[0]);
          // Only allow concat before quotes.
          if (substr($pre_expression, -1) == '&') {
            $pre_expression = rtrim($pre_expression, '&');
          }
          elseif (empty(trim($pre_expression))) {
            $pre_expression = '';
          }
          else {
            $this->setError(TRUE);
            $GLOBALS['redcat_app_errors'][] = 'A string in the submitted expression '
              . 'was joined by something other than "&".';
            return FALSE;
          }
          $process[] = [
            'type' => 'expression',
            'expression' => $this->parseExpressionNoQuotes($pre_expression),
          ];
        }
        $process[] = [
          'type' => 'quote',
          'string' => $string_results[1][$key],
        ];
        $expression_split = $parse[1] ?? '';
        $expression_split = trim($expression_split);
        // Only allow concat after quotes.
        if (substr($expression_split, 0, 1) == '&') {
          $expression_split = ltrim($expression_split, '&');
        }
        elseif (empty(trim($expression_split))) {
          $expression_split = '';
        }
        else {
          $this->setError(TRUE);
          $GLOBALS['redcat_app_errors'][] = 'A string in the submitted expression was '
            . 'joined by something other than "&".';
          return FALSE;
        }
      }
      if (strlen(trim($expression_split)) > 0) {
        $process[] = [
          'type' => 'expression',
          'expression' => $this->parseExpressionNoQuotes($expression_split),
        ];
      }
    }
    else {
      $process[] = [
        'type' => 'expression',
        'expression' => $this->parseExpressionNoQuotes($expression),
      ];
    }

    return $process;
  }

  /**
   * Parse expression no quotes.
   *
   * @param string $expression
   *   The user submitted expression.
   *
   * @return mixed
   *   Return a processing array or FALSE on failure.
   */
  public function parseExpressionNoQuotes(string $expression) {
    $operators = ['*', '/', '+', '-', '&'];
    $replace_operators = ['|*|', '|/|', '|+|', '|-|', '|&|'];
    $expression = str_replace($operators, $replace_operators, trim($expression));
    $process = explode('|', $expression);
    return $process;
  }

  /**
   * Create new CSV data.
   *
   * @param array $process
   *   User submitted process logic.
   * @param string $column
   *   New column name.
   *
   * @return mixed
   *   Return a data array or FALSE on failure.
   */
  public function createData(array $process, string $column = 'new_row') {
    $data = $this->data;
    $columns = $this->getHeaders();
    foreach ($data as $key => $row) {
      // Set new column name.
      if ($key == 0) {
        $data[$key][] = $column;
      }
      // Calculate new data row.
      else {
        $new_value = '';
        foreach ($process as $actions) {
          if ($actions['type'] == 'quote') {
            $new_value .= $actions['string'];
          }
          elseif ($actions['type'] == 'expression') {
            if (count($actions['expression']) == 1) {
              $val = $actions['expression'][0];
              if (!is_numeric($val)) {
                $row_key = array_search($val, $columns);
                if ($row_key === FALSE) {
                  $this->setError(TRUE);
                  $GLOBALS['redcat_app_errors'][] = 'The expression had an error around "'
                    . $val . '".';
                  return FALSE;
                }
                else {
                  $val = $row[$row_key];
                }
              }
              $new_value .= $val;
            }
            else {
              $expression_process = $actions['expression'];
              // Find * and /.
              $expression_process = $this->operatorProcessing(
                $expression_process,
                ['*', '/'],
                $row,
                $columns
              );
              if ($expression_process === FALSE) {
                return FALSE;
              }

              // Find + and -.
              $expression_process = $this->operatorProcessing(
                $expression_process,
                ['+', '-'],
                $row,
                $columns
              );
              if ($expression_process === FALSE) {
                return FALSE;
              }

              // Find &.
              $c_key = $this->operatorKey($expression_process, ['&']);
              $all_ops = ['*', '/', '+', '-', '&'];
              while ($c_key !== FALSE) {
                $new_expression_process = [];
                if (
                  isset($expression_process[$c_key - 1])
                  && isset($expression_process[$c_key + 1])
                ) {
                  $val_1 = trim($expression_process[$c_key - 1]);
                  $val_2 = trim($expression_process[$c_key + 1]);
                  $val = '';
                  if ($val_1 == 'NA' || $val_2 == 'NA') {
                    $val = 'NA';
                  }
                  elseif(in_array($val_1, $all_ops) || in_array($val_2, $all_ops)) {
                    $this->setError(TRUE);
                    $error_string = $expression_process[$c_key - 1];
                    $error_string .= $expression_process[$c_key];
                    $error_string .= $expression_process[$c_key + 1];
                    $GLOBALS['redcat_app_errors'][] = 'The expression had an error around "'
                      . $error_string . '".';
                    return FALSE;
                  }
                  else {
                    $row_key = array_search($val_1, $columns);
                    if ($row_key !== FALSE) {
                      $val_1 = $row[$row_key];
                    }
                    $row_key = array_search($val_2, $columns);
                    if ($row_key !== FALSE) {
                      $val_2 = $row[$row_key];
                    }
                    $val = $val_1 . $val_2;
                  }
                  // Construct new array.
                  if ($c_key > 1) {
                    $new_expression_process = array_slice($expression_process, 0, $c_key - 1);
                  }
                  $new_expression_process[] = $val;
                  if ($c_key < count($expression_process) - 2) {
                    $end_num = count($expression_process) - $c_key - 2;
                    $new_expression_process = array_merge(
                      $new_expression_process,
                      array_slice($expression_process, -1, $end_num)
                    );
                  }
                }
                else {
                  $this->setError(TRUE);
                  $GLOBALS['redcat_app_errors'][] = 'There was a problem with one of the concat'
                    . ' operations (&). Please review your expression.';
                  return FALSE;
                }

                $expression_process = $new_expression_process;
                $c_key = $this->operatorKey($expression_process, ['&']);
              }
              // There should only be one value left.
              if (count($expression_process) == 1) {
                $new_value .= $expression_process[0];
              }
              else {
                // Something broke.
                $this->setError(TRUE);
                $GLOBALS['redcat_app_errors'][] = 'Something went wrong. '
                  . 'Please review your expression.';
                return FALSE;
              }
            }
          }
        }
        $data[$key][] = $new_value;
      }
    }
    return $data;
  }

  /**
   * Get CSV headers.
   *
   * @return array
   *   Return an array of column headers.
   */
  public function getHeaders() {
    $columns = $this->data[0];
    $headers = [];
    foreach ($columns as $header) {
      $headers[] = trim($header);
    }

    return $headers;
  }

  /**
   * Find next oeprator key.
   *
   * @param array $process
   *   Process array.
   * @param array $operators
   *   Operators to search for.
   *
   * @return mixed
   *   Returns the array key or FALSE.
   */
  public function operatorKey(array $process, array $operators) {
    $keys = [];
    foreach ($operators as $operator) {
      $key = array_search($operator, $process);
      if ($key !== FALSE) {
        $keys[] = $key;
      }
    }
    sort($keys);
    return $keys[0] ?? FALSE;
  }

  /**
   * Process the new values and process array.
   *
   * @param array $expression_process
   *   The array of process information.
   * @param array $operators
   *   Array of operators in this loop.
   * @param array $row
   *   Row data used to calculate value.
   * @param array $columns
   *   List of columns in data.
   *
   * @return mixed
   *   Returns new process array or FALSE if fail.
   */
  public function operatorProcessing(
    array $expression_process,
    array $operators,
    array $row,
    array $columns
  ) {
    $m_key = $this->operatorKey($expression_process, $operators);
    $all_ops = ['*', '/', '+', '-', '&'];
    while ($m_key !== FALSE) {
      $new_expression_process = [];
      if (
        isset($expression_process[$m_key - 1])
        && isset($expression_process[$m_key + 1])
      ) {
        $val_1 = trim($expression_process[$m_key - 1]);
        $val_2 = trim($expression_process[$m_key + 1]);
        $val = '';
        if ($val_1 == 'NA' || $val_2 == 'NA') {
          $val = 'NA';
        }
        elseif(in_array($val_1, $all_ops) || in_array($val_2, $all_ops)) {
          $this->setError(TRUE);
          $error_string = $expression_process[$m_key - 1];
          $error_string .= $expression_process[$m_key];
          $error_string .= $expression_process[$m_key + 1];
          $GLOBALS['redcat_app_errors'][] = 'The expression had an error around "'
            . $error_string . '".';
          return FALSE;
        }
        else {
          if (!is_numeric($val_1)) {
            $row_key = array_search($val_1, $columns);
            if ($row_key === FALSE) {
              $this->setError(TRUE);
              $error_string = $expression_process[$m_key - 1];
              $error_string .= $expression_process[$m_key];
              $error_string .= $expression_process[$m_key + 1];
              $GLOBALS['redcat_app_errors'][] = 'The expression had an error around "'
                . $error_string . '".';
              return FALSE;
            }
            else {
              $val_1 = $row[$row_key];
            }
          }
          if (!is_numeric($val_2)) {
            $row_key = array_search($val_2, $columns);
            if ($row_key === FALSE) {
              $this->setError(TRUE);
              $error_string = $expression_process[$m_key - 1];
              $error_string .= $expression_process[$m_key];
              $error_string .= $expression_process[$m_key + 1];
              $GLOBALS['redcat_app_errors'][] = 'The expression had an error around "'
                . $error_string . '".';
              return FALSE;
            }
            else {
              $val_2 = $row[$row_key];
            }
          }

          if (is_numeric($val_1) && is_numeric($val_2)) {
            if ($expression_process[$m_key] == '*') {
              $val = $val_1 * $val_2;
            }
            elseif($expression_process[$m_key] == '/') {
              $val = $val_1 / $val_2;
            }
            elseif($expression_process[$m_key] == '+') {
              $val = $val_1 + $val_2;
            }
            elseif($expression_process[$m_key] == '-') {
              $val = $val_1 - $val_2;
            }
          }
          else {
            $val = 'NA';
          }
        }
        // Construct new array.
        if ($m_key > 1) {
          $new_expression_process = array_slice($expression_process, 0, $m_key - 1);
        }
        $new_expression_process[] = $val;
        if ($m_key < count($expression_process) - 2) {
          $end_num = count($expression_process) - $m_key - 2;
          $new_expression_process = array_merge(
            $new_expression_process,
            array_slice($expression_process, -1, $end_num)
          );
        }
      }
      else {
        $this->setError(TRUE);
        $GLOBALS['redcat_app_errors'][] = 'There was a problem with one of the '
          . 'operations (* / + -). Please review your expression.';
        return FALSE;
      }

      $expression_process = $new_expression_process;
      $m_key = $this->operatorKey($expression_process, $operators);
    }
    return $expression_process;
  }

}
