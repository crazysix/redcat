<?php

namespace CodeChallenge\Form;

/**
 * Form base.
 */
class Form {

  /**
   * Get form id.
   *
   * @return string
   *   The form ID.
   */
  public function getFormId() {
    return 'redcat-form';
  }

  /**
   * Build Form
   *
   * @return array
   *   Array of form elements.
   */
  public function buildForm() {
    return [];
  }

  /**
   * Render form.
   *
   * @return string
   *   Rendered form.
   */
  public function renderForm() {
    $form_id = $this->getFormId();
    $form = $this->buildform();

    $form_output = '<form method="post" action="' . $_SERVER['REQUEST_URI'] . '" ';
    $form_output .= 'name="' . $form_id . '" id="' . $form_id . '"';
    $form_output .= (!empty($form['#file_upload']) ? ' enctype="multipart/form-data"' : '') . '>';

    // Render form elements.
    foreach ($form as $name => $element) {
      if (substr($name, 0, 1) != '#') {
        if (!isset($element['#type']) || $element['#type'] == 'markup') {
          $form_output .= '<div name="' . $name . '">' . ($element['#markup'] ?? '') . '</div>';
        }
        elseif ($element['#type'] == 'textfield') {
          $size = $element['#size'] ?? 60;
          $maxlength = $element['#maxlength'] ?? 128;
          $form_output .= '<div class="field-' . $element['#type'] . '">';
          $form_output .= '<label>' . ($element['#title'] ?? '') . '</label>';
          $form_output .= '<input name="' . $name . '" type="text" ';
          $form_output .= 'size="' . $size . '" maxlength="' . $maxlength . '" ';
          $form_output .= 'value="' . ($element['#default_value'] ?? '') . '" ';
          $form_output .= 'id="edit-' . str_replace([' ', '_'], '-', $name) . '" />';
          if (!empty($element['#description'])) {
            $form_output .= '<div class="description">' . $element['#description'] . '</div>';
          }
          $form_output .= '</div>';
        }
        elseif ($element['#type'] == 'textarea') {
          $rows = $element['#rows'] ?? 5;
          $cols = $element['#cols'] ?? 60;
          $form_output .= '<div class="field-' . $element['#type'] . '">';
          $form_output .= '<label>' . ($element['#title'] ?? '') . '</label>';
          $form_output .= '<textarea name="' . $name . '" ';
          $form_output .= 'rows="' . $rows . '" cols="' . $cols . '" ';
          $form_output .= 'id="edit-' . str_replace([' ', '_'], '-', $name) . '">';
          $form_output .= $element['#default_value'] ?? '';
          $form_output .= '</textarea>';
          if (!empty($element['#description'])) {
            $form_output .= '<div class="description">' . $element['#description'] . '</div>';
          }
          $form_output .= '</div>';
        }
        elseif ($element['#type'] == 'checkbox') {
          $form_output .= '<div class="field-' . $element['#type'] . '">';
          $form_output .= '<input name="' . $name . '" type="checkbox" ';
          $form_output .= 'value="1" ' . (!empty($element['#default_value']) ? 'checked="checked" ': '');
          $form_output .= 'id="edit-' . str_replace([' ', '_'], '-', $name) . '" />';
          $form_output .= '<label>' . ($element['#title'] ?? '') . '</label>';
          if (!empty($element['#description'])) {
            $form_output .= '<div class="description">' . $element['#description'] . '</div>';
          }
          $form_output .= '</div>';
        }
        elseif ($element['#type'] == 'file') {
          $accept = $element['#extensions'] ?? '.csv';
          $form_output .= '<div class="field-' . $element['#type'] . '">';
          $form_output .= '<label>' . ($element['#title'] ?? '') . '</label>';
          $form_output .= '<input name="' . $name . '" type="file" ';
          $form_output .= 'accept="' . $accept . '" ';
          $form_output .= 'id="edit-' . str_replace([' ', '_'], '-', $name) . '" />';
          if (!empty($element['#description'])) {
            $form_output .= '<div class="description">' . $element['#description'] . '</div>';
          }
          $form_output .= '</div>';
        }
        elseif ($element['#type'] == 'submit') {
          $form_output .= '<div class="field-' . $element['#type'] . '">';
          $form_output .= '<input name="' . $name . '" type="submit" class="form-actions" ';
          $form_output .= 'value="' . ($element['#title'] ?? 'Submit') . '" ';
          $form_output .= 'id="edit-' . str_replace([' ', '_'], '-', $name) . '" />';
          $form_output .= '</div>';
        }
      }
    }

    $form_output .= '</form>';

    return $form_output;
  }

  /**
   * Get submitted values.
   *
   * @param string $key
   *   The value key of a submitted field.
   */
  public function getValues(string $key = '') {
    if (!empty($key)) {
      return $_POST[$key] ?? NULL;
    }

    return $_POST ?? [];
  }

  /**
   * Validate submitted values.
   *
   * @param array $values
   *   Submitted values to be validated.
   *
   * @return bool
   *   Returns TRUE if values are valid.
   */
  public function validate(array $values = []) {
    return TRUE;
  }

  /**
   * Form submit handler.
   */
  public function submit() {
    $values = $this->getValues();
  }

}
