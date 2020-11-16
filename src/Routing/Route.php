<?php

namespace CodeChallenge\Routing;

/**
 * Router class.
 */
class Route {
  
  /**
   * The routes used in this app.
   *
   * @var array
   */
  protected $routes = [];

  /**
   * Add application route.
   *
   * @param string $path
   *   The path requested.
   * @param callable $function
   *   The function to run on match.
   * @param string $method
   *   The method used.
   */
  public function add($path, $function, $method = 'get') {
    $this->routes[] = [
      'path' => $path,
      'function' => $function,
      'method' => $method,
    ];
  }

  /**
   * Run router.
   *
   * @param string $base
   *   The base path.
   */
  public function run($base = '/') {
    $parsed_url = parse_url($_SERVER['REQUEST_URI']);
    $path = $parsed_url['path'] ?? '/';

    $method = strtolower($_SERVER['REQUEST_METHOD']);

    $path_match = FALSE;
    $route_match = FALSE;

    foreach ($this->routes as $route) {
      // Add base to string.
      if ($base != '' && $base != '/') {
        $route['path'] = '(' . $base . ')' . $route['path'];
      }

      // Add string start and stop.
      $route['path'] = '^' . $route['path'] . '$';

      if (preg_match('#' . $route['path'] . '#', $path, $matches)) {
        $path_match = TRUE;

        // Check method.
        if ($method == $route['method']) {
          array_shift($matches);

          if ($base != '' && $base != '/') {
            array_shift($matches);
          }

          call_user_func_array($route['function'], $matches);

          $route_match = TRUE;

          // Match found.
          break;
        }
      }
    }

    // Match not found.
    if (!$route_match) {
      // Path exists.
      if ($path_match) {
        $this->badMethod();
      }
      else {
        $this->noPathMatch();
      }
    }
  }

  /**
   * No path match.
   */
  public function noPathMatch() {
    header("HTTP/1.0 404 Not Found");
  }

  /**
   * Method not allowed.
   */
  public function badMethod() {
    header("HTTP/1.0 405 Method Not Allowed");
  }

}
