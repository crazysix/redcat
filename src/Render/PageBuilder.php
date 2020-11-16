<?php

namespace CodeChallenge\Render;

class PageBuilder {

  /**
   * The JS files to add.
   *
   * @var array
   */
  public $js = [
    '/js/app.js',
  ];

  /**
   * The CSS files to add.
   *
   * @var array
   */
  public $css = [
    '/css/style.css',
  ];

  /**
   * The default menu.
   *
   * @var array
   */
  public $menu = [
    [
      'link' => '/',
      'title' => 'Home',
    ],
    [
      'link' => '/upload',
      'title' => 'Upload Data',
    ],
  ];

  /**
   * Build page.
   *
   * @param array $build
   *   The build content of the page.
   */
  public function buildPage(array $build) {
    echo $this->buildHeader($build);
    if (!empty($build['content'])) {
      echo $build['content'];
    }
    echo $this->buildFooter();
  }

  /**
   * Build header.
   *
   * @param array $build
   *   The build content of the page.
   *
   * @return string
   *   Header HTML.
   */
  public function buildHeader(array $build) {
    $header = file_get_contents(BASE_PATH . '/src/Render/templates/header.html');

    // Add new JS files.
    if (!empty($build['js'])) {
      foreach ($build['js'] as $file) {
        $this->addJS($file);
      }
    }

    // Add new CSS files.
    if (!empty($build['css'])) {
      foreach ($build['css'] as $file) {
        $this->addCSS($file);
      }
    }

    // Check for menu items.
    if (!empty($build['menu'])) {
      $this->addMenuItems($build['menu']);
    }

    $header_vars = [
      '<%title%>' => $build['title'] ?? 'RedCat Code Challenge',
      '<%scripts%>' => $this->buildJS(),
      '<%styles%>' => $this->buildCSS(),
      '<%menu%>' => $this->buildMenu(),
    ];

    $header = str_replace(array_keys($header_vars), $header_vars, $header);

    return $header;
  }

  /**
   * Build footer.
   *
   * @return string
   *   Footer HTML.
   */
  public function buildFooter() {
    $footer = file_get_contents(BASE_PATH . '/src/Render/templates/footer.html');

    return $footer;
  }

  /**
   * Build JS output.
   *
   * @return string
   *   JS header string.
   */
  public function buildJS() {
    $js = '';
    foreach ($this->js as $file) {
      if (!empty($file)) {
        $js .= '<script type="text/javascript" src="' . $file . '"></script>' . PHP_EOL;
      }
    }

    return $js;
  }

  /**
   * Add JS file.
   *
   * @param string $file
   *   JS file to be added to rendered page.
   */
  public function addJS(string $file) {
    if (!empty($file)) {
      $this->js[] = $file;
    }
  }

  /**
   * Build CSS output.
   *
   * @return string
   *   CSS header string.
   */
  public function buildCSS() {
    $css = '';
    foreach ($this->css as $file) {
      if (!empty($file)) {
        $css .= '<link rel="stylesheet" href="' . $file . '">' . PHP_EOL;
      }
    }

    return $css;
  }

  /**
   * Add CSS file.
   *
   * @param string $file
   *   CSS file to be added to rendered page.
   */
  public function addCSS(string $file) {
    if (!empty($file)) {
      $this->css[] = $file;
    }
  }

  /**
   * Build menu output.
   *
   * @return string
   *   Menu list items.
   */
  public function buildMenu() {
    $menu = '';
    foreach ($this->menu as $link) {
      if (!empty($link['title']) && !empty($link['link'])) {
        $menu .= '<li><a href="' . $link['link'] . '">' . $link['title'] . '</a></li>' . PHP_EOL;
      }
    }

    return $menu;
  }

  /**
   * Add menu item.
   *
   * @param array $items
   *   Menu items to be added to the menu.
   */
  public function addMenuItems(array $items) {
    array_push($this->menu, $items);
  }

}
